/**
 * Candle Tracker
 * A lightweight analytics tracker for Laravel Candle package
 */
(function() {
    // Configuration - These values will be replaced by the server
    const config = {
      endpoint: '/api/analytics/collect',
      siteId: '{{SITE_ID}}',
      apiKey: '{{API_KEY}}',
      trackClicks: {{TRACK_CLICKS}},
      trackForms: {{TRACK_FORMS}},
      trackRouteChanges: {{TRACK_ROUTE_CHANGES}},
      cookieTimeout: {{COOKIE_TIMEOUT}},
      domain: window.location.hostname
    };

    // State management
    const state = {
      sessionId: null,
      userId: null,
      pageviewSent: false,
      queue: [],
      flushTimer: null
    };

    // Utility functions
    const utils = {
      // Generate a UUID
      uuid: function() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
          var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
          return v.toString(16);
        });
      },

      // Set a cookie
      setCookie: function(name, value, days) {
        const expires = new Date();
        expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = name + '=' + value + ';expires=' + expires.toUTCString() + ';path=/;domain=' + (config.domain || '');
      },

      // Get a cookie
      getCookie: function(name) {
        const nameEQ = name + '=';
        const cookies = document.cookie.split(';');
        for (let i = 0; i < cookies.length; i++) {
          let cookie = cookies[i];
          while (cookie.charAt(0) === ' ') {
            cookie = cookie.substring(1, cookie.length);
          }
          if (cookie.indexOf(nameEQ) === 0) {
            return cookie.substring(nameEQ.length, cookie.length);
          }
        }
        return null;
      },

      // Delete a cookie
      deleteCookie: function(name) {
        document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:01 GMT;path=/;domain=' + (config.domain || '');
      },

      // Get the path from a URL
      getPath: function(url) {
        const a = document.createElement('a');
        a.href = url;
        return a.pathname;
      },

      // Check if a URL is an external link
      isExternal: function(url) {
        const a = document.createElement('a');
        a.href = url;
        return a.hostname !== window.location.hostname;
      },

      // Check if a URL is a download link
      isDownload: function(url) {
        const extensions = ['pdf', 'zip', 'rar', 'tar', 'gz', 'dmg', 'iso', 'exe', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
        const a = document.createElement('a');
        a.href = url;
        const pathSplit = a.pathname.split('.');

        if (pathSplit.length > 1) {
          const extension = pathSplit.pop().toLowerCase();
          return extensions.indexOf(extension) !== -1;
        }

        return false;
      },

      // Get the timestamp in ISO format
      timestamp: function() {
        return new Date().toISOString();
      },

      // Safely parse JSON
      parseJSON: function(str) {
        try {
          return JSON.parse(str);
        } catch (e) {
          return null;
        }
      },

      // Get browser language
      getBrowserLanguage: function() {
        return navigator.language || navigator.userLanguage;
      },

      // Get screen dimensions
      getScreenDimensions: function() {
        return {
          width: window.screen.width,
          height: window.screen.height
        };
      }
    };

    // Analytics API
    const analytics = {
      // Initialize the tracker
      init: function() {
        // Get or create session ID
        state.sessionId = utils.getCookie('sa_session_id');
        if (!state.sessionId) {
          state.sessionId = utils.uuid();
          utils.setCookie('sa_session_id', state.sessionId, 1); // 1 day
        }

        // Get or create user ID (for tracking unique users across sessions)
        state.userId = utils.getCookie('sa_user_id');
        if (!state.userId) {
          state.userId = utils.uuid();
          utils.setCookie('sa_user_id', state.userId, config.cookieTimeout || 30); // Default to 30 days
        }

        // Track initial pageview
        if (!state.pageviewSent) {
          this.trackPageview();
          state.pageviewSent = true;
        }

        // Set up event handlers
        this.setupEventHandlers();

        // Set up interval for flushing queue
        state.flushTimer = setInterval(this.flush.bind(this), 10000); // Every 10 seconds
      },

      // Setup event handlers for automatic tracking
      setupEventHandlers: function() {
        // Track clicks if enabled
        if (config.trackClicks) {
          document.addEventListener('click', (event) => {
            const target = event.target.closest('a, button, [data-sa-click]');
            if (!target) return;

            if (target.tagName === 'A') {
              // Track link clicks
              const href = target.getAttribute('href') || '';
              const isExternal = utils.isExternal(href);
              const isDownload = utils.isDownload(href);
              const linkText = target.innerText || target.textContent || '';

              this.track('click', {
                element: 'a',
                href: href,
                text: linkText.trim().substring(0, 100),
                external: isExternal,
                download: isDownload
              });

              // Delay navigation for external links to allow event tracking
              if (isExternal && href.indexOf('javascript:') !== 0 && href.indexOf('#') !== 0 && !event.ctrlKey && !event.metaKey) {
                event.preventDefault();
                setTimeout(() => {
                  window.location.href = href;
                }, 150);
              }
            } else if (target.tagName === 'BUTTON' || target.hasAttribute('data-sa-click')) {
              // Track button clicks
              const buttonText = target.innerText || target.textContent || '';
              const buttonId = target.id || '';
              const buttonClass = target.className || '';

              this.track('click', {
                element: target.tagName.toLowerCase(),
                id: buttonId,
                class: buttonClass,
                text: buttonText.trim().substring(0, 100)
              });
            }
          });
        }

        // Track form submissions if enabled
        if (config.trackForms) {
          document.addEventListener('submit', (event) => {
            const form = event.target;
            const formId = form.id || '';
            const formAction = form.action || '';
            const formMethod = form.method || 'get';

            this.track('form_submit', {
              id: formId,
              action: formAction,
              method: formMethod,
              form_elements: form.elements.length
            });
          });
        }

        // Track route changes for SPAs if enabled
        if (config.trackRouteChanges) {
          // Track history changes
          const originalPushState = window.history.pushState;
          const originalReplaceState = window.history.replaceState;

          window.history.pushState = function() {
            originalPushState.apply(this, arguments);
            analytics.trackPageview();
          };

          window.history.replaceState = function() {
            originalReplaceState.apply(this, arguments);
            analytics.trackPageview();
          };

          // Track browser back/forward buttons
          window.addEventListener('popstate', () => {
            analytics.trackPageview();
          });
        }

        // Track when the user leaves the page
        window.addEventListener('beforeunload', () => {
          this.track('page_exit', {
            time_spent: Math.round((new Date() - state.pageLoadTime) / 1000)
          });

          // Flush queue synchronously
          this.flush(true);
        });
      },

      // Track a pageview
      trackPageview: function(customProperties = {}) {
        state.pageLoadTime = new Date();

        this.track('pageview', {
          page: window.location.pathname,
          url: window.location.href,
          title: document.title,
          referrer: document.referrer,
          ...customProperties
        });
      },

      // Track a custom event
      track: function(eventName, properties = {}) {
        const event = {
          event_name: eventName,
          session_id: state.sessionId,
          user_id: state.userId,
          timestamp: utils.timestamp(),
          url: window.location.href,
          language: utils.getBrowserLanguage(),
          ...utils.getScreenDimensions(),
          properties: properties
        };

        // Add to queue
        state.queue.push(event);

        // Flush immediately if queue is getting large
        if (state.queue.length >= 10) {
          this.flush();
        }
      },

      // Flush the queue
      flush: function(sync = false) {
        if (state.queue.length === 0) return;

        const events = [...state.queue];
        state.queue = [];

        const data = {
          api_key: config.apiKey,
          domain: config.domain,
          events: events
        };

        if (sync) {
          // For synchronous sending (on page unload)
          if (navigator.sendBeacon) {
            navigator.sendBeacon(
              config.endpoint,
              JSON.stringify(data)
            );
          } else {
            // Fallback to synchronous XHR
            const xhr = new XMLHttpRequest();
            xhr.open('POST', config.endpoint, false); // Synchronous
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.send(JSON.stringify(data));
          }
        } else {
          // For asynchronous sending
          fetch(config.endpoint, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify(data),
            keepalive: true
          }).catch(function(error) {
            // If sending fails, add events back to the queue
            state.queue = [...events, ...state.queue];
          });
        }
      }
    };

    // Initialize the tracker
    analytics.init();

    // Export the analytics API
    window.Candle = {
      track: analytics.track.bind(analytics),
      trackPageview: analytics.trackPageview.bind(analytics)
    };
  })();
