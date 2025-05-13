/**
 * Candle Tracker - Advanced User Behavior Analytics
 * Comprehensive tracking for website analytics, debugging and user behavior insights
 * v1.0.0
 */

(function() {
    const config = {
      endpoint: '{{ route("candle.collect") }}',
      siteId: '{{ $siteId }}',
      apiKey: '{{ $apiKey }}',
      trackClicks: {{ $trackClicks ? 'true' : 'false' }},
      trackForms: {{ $trackForms ? 'true' : 'false' }},
      trackRouteChanges: {{ $trackRouteChanges ? 'true' : 'false' }},
      trackErrors: true,
      trackPerformance: true,
      trackScroll: true,
      trackHover: true,
      trackVisibility: true,
      trackNetwork: true,
      trackConsole: true,
      trackMedia: true,
      sampleRate: 100, // Percentage of events to capture (1-100)
      cookieTimeout: {{ $cookieTimeout }},
      domain: window.location.hostname
    };

    const state = {
      sessionId: null,
      userId: null,
      queue: [],
      flushTimer: null,
      pageLoadTime: new Date(),
      pageLeaveIntent: 0,
      lastActivity: new Date(),
      maxScrollDepth: 0,
      visitCount: 0,
      pageSequence: 0,
      interactionCount: 0,
      referrer: document.referrer,
      entryPage: location.pathname,
      deviceId: null,
      debugMode: (new URLSearchParams(window.location.search)).has('candle_debug'),
      idleTimeout: null,
      performanceMetrics: {},
      networkRequests: []
    };

    const utils = {
      uuid: () => 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
        const r = Math.random() * 16 | 0;
        const v = c === 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
      }),
      setCookie: (name, value, days) => {
        const expires = new Date(Date.now() + days * 864e5).toUTCString();
        document.cookie = `${name}=${value};expires=${expires};path=/;domain=${config.domain}`;
      },
      getCookie: name => {
        return document.cookie.split('; ').reduce((r, v) => {
          const parts = v.split('=');
          return parts[0] === name ? parts[1] : r
        }, null);
      },
      timestamp: () => new Date().toISOString(),
      screenSize: () => ({
        width: window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth,
        height: window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight,
        screen_width: screen.width,
        screen_height: screen.height,
        pixel_ratio: window.devicePixelRatio || 1
      }),
      viewportInfo: () => {
        const viewport = {
          ...utils.screenSize(),
          scroll_top: window.scrollY || document.documentElement.scrollTop,
          scroll_left: window.scrollX || document.documentElement.scrollLeft,
          max_scroll_depth: state.maxScrollDepth
        };

        // Calculate visible percentage of page
        const pageHeight = Math.max(
          document.body.scrollHeight, document.body.offsetHeight,
          document.documentElement.clientHeight, document.documentElement.scrollHeight,
          document.documentElement.offsetHeight
        );
        viewport.visible_percentage = Math.min(100, Math.round((viewport.height / pageHeight) * 100));

        return viewport;
      },
      browserInfo: () => {
        // Get browser name and version
        const userAgent = navigator.userAgent;
        let browser = "Unknown";
        let version = "Unknown";

        // Detect browser
        if (userAgent.indexOf("Firefox") > -1) {
          browser = "Firefox";
          version = userAgent.match(/Firefox\/([0-9.]+)/)[1];
        } else if (userAgent.indexOf("SamsungBrowser") > -1) {
          browser = "Samsung Internet";
          version = userAgent.match(/SamsungBrowser\/([0-9.]+)/)[1];
        } else if (userAgent.indexOf("Opera") > -1 || userAgent.indexOf("OPR") > -1) {
          browser = "Opera";
          version = userAgent.match(/(?:Opera|OPR)\/([0-9.]+)/)[1];
        } else if (userAgent.indexOf("Trident") > -1) {
          browser = "Internet Explorer";
          version = userAgent.match(/rv:([0-9.]+)/)[1];
        } else if (userAgent.indexOf("Edge") > -1) {
          browser = "Edge (Legacy)";
          version = userAgent.match(/Edge\/([0-9.]+)/)[1];
        } else if (userAgent.indexOf("Edg") > -1) {
          browser = "Edge Chromium";
          version = userAgent.match(/Edg\/([0-9.]+)/)[1];
        } else if (userAgent.indexOf("Chrome") > -1) {
          browser = "Chrome";
          version = userAgent.match(/Chrome\/([0-9.]+)/)[1];
        } else if (userAgent.indexOf("Safari") > -1) {
          browser = "Safari";
          version = userAgent.match(/Version\/([0-9.]+)/)[1];
        }

        return {
          browser,
          browser_version: version,
          user_agent: userAgent,
          language: navigator.language || navigator.userLanguage,
          languages: navigator.languages ? navigator.languages.join(',') : null,
          platform: navigator.platform,
          vendor: navigator.vendor,
          cookie_enabled: navigator.cookieEnabled,
          do_not_track: navigator.doNotTrack === '1' || window.doNotTrack === '1'
        };
      },
      deviceInfo: () => {
        // Attempt to detect device type
        const userAgent = navigator.userAgent || navigator.vendor || window.opera;

        let device_type = "desktop";
        if (/android/i.test(userAgent)) {
          device_type = "mobile";
        } else if (/iPad|iPhone|iPod/.test(userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1)) {
          device_type = /iPad/.test(userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1) ? "tablet" : "mobile";
        } else if (/tablet|Tablet|PlayBook|Silk|Android(?!.*Mobile)/.test(userAgent)) {
          device_type = "tablet";
        }

        // Detect OS
        let os = "Unknown";
        let os_version = "Unknown";

        if (/Windows NT 10.0/.test(userAgent)) os = "Windows 10";
        else if (/Windows NT 6.3/.test(userAgent)) os = "Windows 8.1";
        else if (/Windows NT 6.2/.test(userAgent)) os = "Windows 8";
        else if (/Windows NT 6.1/.test(userAgent)) os = "Windows 7";
        else if (/Windows NT 6.0/.test(userAgent)) os = "Windows Vista";
        else if (/Windows NT 5.1|Windows XP/.test(userAgent)) os = "Windows XP";
        else if (/Windows NT 5.0|Windows 2000/.test(userAgent)) os = "Windows 2000";
        else if (/Mac OS X/.test(userAgent)) {
          os = "macOS";
          const match = userAgent.match(/Mac OS X ([0-9_]+)/);
          if (match) os_version = match[1].replace(/_/g, '.');
        }
        else if (/Android/.test(userAgent)) {
          os = "Android";
          const match = userAgent.match(/Android ([0-9.]+)/);
          if (match) os_version = match[1];
        }
        else if (/iOS|iPhone OS|iPad;.*CPU/.test(userAgent)) {
          os = "iOS";
          const match = userAgent.match(/OS ([0-9_]+)/);
          if (match) os_version = match[1].replace(/_/g, '.');
        }
        else if (/Linux/.test(userAgent)) os = "Linux";

        // Connection info
        const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection || {};

        return {
          device_type,
          os,
          os_version,
          connection_type: connection.effectiveType || null,
          connection_downlink: connection.downlink || null,
          connection_rtt: connection.rtt || null,
          battery: navigator.getBattery ? "supported" : "unsupported",
          touch_points: navigator.maxTouchPoints || 0
        };
      },
      sessionInfo: () => ({
        session_id: state.sessionId,
        user_id: state.userId,
        device_id: state.deviceId,
        page_sequence: state.pageSequence,
        visit_count: state.visitCount,
        interaction_count: state.interactionCount,
        time_on_page: Math.round((new Date() - state.pageLoadTime) / 1000),
        idle_time: Math.round((new Date() - state.lastActivity) / 1000),
        entry_page: state.entryPage,
        referrer: state.referrer
      }),
      getElementPath: (element) => {
        if (!element || !element.tagName) return '';

        let path = element.tagName.toLowerCase();

        // Add ID if it exists
        if (element.id) {
          path += `#${element.id}`;
          return path;
        }

        // Add classes (limit to 3)
        if (element.className && typeof element.className === 'string') {
          const classes = element.className.trim().split(/\s+/);
          if (classes.length > 0 && classes[0] !== '') {
            path += `.${classes.slice(0, 3).join('.')}`;
          }
        }

        // Add order among siblings
        if (element.parentNode) {
          const siblings = Array.from(element.parentNode.children);
          const index = siblings.indexOf(element) + 1;
          if (siblings.length > 1) {
            path += `:nth-child(${index})`;
          }
        }

        return path;
      },
      getFullElementPath: (element, maxDepth = 5) => {
        if (!element || !element.tagName) return '';

        let path = [];
        let current = element;
        let depth = 0;

        while (current && current.tagName && depth < maxDepth && current !== document.body && current !== document.documentElement) {
          path.unshift(utils.getElementPath(current));
          current = current.parentNode;
          depth++;
        }

        return path.join(' > ');
      },
      getElementAttributes: (element) => {
        if (!element || !element.tagName) return {};

        const attributes = {
          tag: element.tagName.toLowerCase(),
          id: element.id || null,
          class: element.className || null,
          text: (element.innerText || '').trim().substring(0, 100),
          href: element.getAttribute('href') || null,
          src: element.getAttribute('src') || null,
          alt: element.getAttribute('alt') || null,
          title: element.getAttribute('title') || null,
          name: element.getAttribute('name') || null,
          value: element.tagName === 'INPUT' ? element.value?.length || 0 : null,
          type: element.getAttribute('type') || null,
          role: element.getAttribute('role') || null,
          aria_label: element.getAttribute('aria-label') || null,
          disabled: element.disabled || null,
          checked: element.checked !== undefined ? element.checked : null,
          placeholder: element.getAttribute('placeholder') || null,
          path: utils.getFullElementPath(element),
          x: element.getBoundingClientRect().left + window.scrollX || 0,
          y: element.getBoundingClientRect().top + window.scrollY || 0,
          width: element.offsetWidth || 0,
          height: element.offsetHeight || 0,
          visible: utils.isElementVisible(element)
        };

        // Add data attributes
        const dataAttributes = {};
        for (const key in element.dataset) {
          if (Object.prototype.hasOwnProperty.call(element.dataset, key)) {
            dataAttributes[key] = element.dataset[key];
          }
        }

        if (Object.keys(dataAttributes).length > 0) {
          attributes.dataset = dataAttributes;
        }

        return attributes;
      },
      isElementVisible: (element) => {
        if (!element || !element.getBoundingClientRect) return false;

        const rect = element.getBoundingClientRect();
        return (
          rect.top >= 0 &&
          rect.left >= 0 &&
          rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
          rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
      },
      getSafeObj: (obj) => {
        try {
          // Remove circular references and functions
          return JSON.parse(JSON.stringify(obj));
        } catch (e) {
          return { error: "Could not serialize object" };
        }
      },
      shouldSample: () => {
        return Math.random() * 100 <= config.sampleRate;
      },
      getPerformanceMetrics: () => {
        if (!window.performance || !window.performance.timing) {
          return { supported: false };
        }

        const timing = window.performance.timing;
        const navigationStart = timing.navigationStart;

        return {
          load_time: timing.loadEventEnd - navigationStart,
          dom_interactive: timing.domInteractive - navigationStart,
          dom_complete: timing.domComplete - navigationStart,
          first_paint: state.performanceMetrics.firstPaint || null,
          first_contentful_paint: state.performanceMetrics.firstContentfulPaint || null,
          largest_contentful_paint: state.performanceMetrics.largestContentfulPaint || null,
          cumulative_layout_shift: state.performanceMetrics.cumulativeLayoutShift || null,
          first_input_delay: state.performanceMetrics.firstInputDelay || null,
          time_to_first_byte: timing.responseStart - timing.requestStart,
          dns_lookup: timing.domainLookupEnd - timing.domainLookupStart,
          tcp_connect: timing.connectEnd - timing.connectStart,
          redirect_time: timing.redirectEnd - timing.redirectStart,
          ttfb: timing.responseStart - navigationStart,
          resource_count: window.performance.getEntriesByType ? window.performance.getEntriesByType('resource').length : null
        };
      },
      fingerprint: () => {
        // Create a simple device fingerprint
        const components = [
          navigator.userAgent,
          navigator.language,
          screen.colorDepth,
          new Date().getTimezoneOffset(),
          navigator.hardwareConcurrency,
          screen.width + 'x' + screen.height,
          navigator.plugins?.length || 0
        ];

        // Simple hash function
        const hash = s => {
          let h = 0;
          for (let i = 0; i < s.length; i++) {
            h = Math.imul(31, h) + s.charCodeAt(i) | 0;
          }
          return h.toString(16);
        };

        return hash(components.join('~~~'));
      }
    };

    const analytics = {
      init() {
        // Initialize device identification
        state.deviceId = utils.getCookie('sa_device_id') || utils.fingerprint();
        utils.setCookie('sa_device_id', state.deviceId, 365);

        // Initialize session
        state.sessionId = utils.getCookie('sa_session_id') || utils.uuid();
        utils.setCookie('sa_session_id', state.sessionId, 1);

        // Initialize user ID
        state.userId = utils.getCookie('sa_user_id') || utils.uuid();
        utils.setCookie('sa_user_id', state.userId, config.cookieTimeout || 30);

        // Count visits and page sequence
        state.visitCount = parseInt(utils.getCookie('sa_visit_count') || '0', 10) + 1;
        utils.setCookie('sa_visit_count', state.visitCount.toString(), config.cookieTimeout || 30);

        state.pageSequence = parseInt(utils.getCookie('sa_page_sequence') || '0', 10) + 1;
        utils.setCookie('sa_page_sequence', state.pageSequence.toString(), 1);

        if (state.debugMode) {
          console.log('[Candle] Initializing with config:', config);
          console.log('[Candle] Session state:', state);
        }

        this.capturePerformanceMetrics();
        this.trackPageview();
        this.bindEvents();

        // Set up regular tracking
        state.flushTimer = setInterval(() => this.flush(), 10000);
        setInterval(() => this.trackHeartbeat(), 30000);

        // Set up activity monitoring
        state.idleTimeout = setTimeout(() => this.track('user_idle', { idle_time: 30 }), 30000);
      },

      capturePerformanceMetrics() {
        if (!config.trackPerformance) return;

        if (window.PerformanceObserver && PerformanceObserver.supportedEntryTypes) {
          // First Paint & First Contentful Paint
          if (PerformanceObserver.supportedEntryTypes.includes('paint')) {
            const paintObserver = new PerformanceObserver((entries) => {
              for (const entry of entries.getEntries()) {
                if (entry.name === 'first-paint') {
                  state.performanceMetrics.firstPaint = entry.startTime;
                } else if (entry.name === 'first-contentful-paint') {
                  state.performanceMetrics.firstContentfulPaint = entry.startTime;
                }
              }
            });
            paintObserver.observe({ type: 'paint', buffered: true });
          }

          // Largest Contentful Paint
          if (PerformanceObserver.supportedEntryTypes.includes('largest-contentful-paint')) {
            const lcpObserver = new PerformanceObserver((entries) => {
              const lastEntry = entries.getEntries().pop();
              if (lastEntry) {
                state.performanceMetrics.largestContentfulPaint = lastEntry.startTime;
              }
            });
            lcpObserver.observe({ type: 'largest-contentful-paint', buffered: true });
          }

          // Cumulative Layout Shift
          if (PerformanceObserver.supportedEntryTypes.includes('layout-shift')) {
            let cumulativeLayoutShift = 0;
            const layoutShiftObserver = new PerformanceObserver((entries) => {
              for (const entry of entries.getEntries()) {
                if (!entry.hadRecentInput) {
                  cumulativeLayoutShift += entry.value;
                }
              }
              state.performanceMetrics.cumulativeLayoutShift = cumulativeLayoutShift;
            });
            layoutShiftObserver.observe({ type: 'layout-shift', buffered: true });
          }

          // First Input Delay
          if (PerformanceObserver.supportedEntryTypes.includes('first-input')) {
            const fidObserver = new PerformanceObserver((entries) => {
              const firstInput = entries.getEntries()[0];
              if (firstInput) {
                state.performanceMetrics.firstInputDelay = firstInput.processingStart - firstInput.startTime;
              }
            });
            fidObserver.observe({ type: 'first-input', buffered: true });
          }

          // Network requests
          if (config.trackNetwork && PerformanceObserver.supportedEntryTypes.includes('resource')) {
            const resourceObserver = new PerformanceObserver((entries) => {
              for (const entry of entries.getEntries()) {
                // Filter out analytics requests to avoid recursion
                if (entry.name.includes(config.endpoint)) continue;

                // Add network request to tracking
                state.networkRequests.push({
                  url: entry.name,
                  resource_type: entry.initiatorType,
                  duration: entry.duration,
                  size: entry.transferSize || 0,
                  protocol: entry.nextHopProtocol,
                  start_time: entry.startTime,
                  timestamp: utils.timestamp()
                });

                // Keep only the latest 50 requests
                if (state.networkRequests.length > 50) {
                  state.networkRequests.shift();
                }
              }
            });
            resourceObserver.observe({ type: 'resource', buffered: true });
          }
        }
      },

      bindEvents() {
        // Reset user activity tracker
        const resetIdleTimeout = () => {
          state.lastActivity = new Date();
          clearTimeout(state.idleTimeout);
          state.idleTimeout = setTimeout(() => this.track('user_idle', { idle_time: 30 }), 30000);
        };

        // Track user activity
        document.addEventListener('mousemove', resetIdleTimeout);
        document.addEventListener('keydown', resetIdleTimeout);
        document.addEventListener('scroll', () => {
          resetIdleTimeout();
          this.handleScroll();
        });
        document.addEventListener('touchstart', resetIdleTimeout);

        // Track clicks
        if (config.trackClicks) {
          document.addEventListener('click', this.handleClick.bind(this));
        }

        // Track forms
        if (config.trackForms) {
          document.addEventListener('submit', this.handleForm.bind(this));
          document.addEventListener('focusout', this.handleInput.bind(this), true);
          document.addEventListener('change', this.handleChange.bind(this));
        }

        // Track scroll
        if (config.trackScroll) {
          window.addEventListener('scroll', this.handleScroll.bind(this), { passive: true });
        }

        // Track hover (debounced)
        if (config.trackHover) {
          let hoverTimer;
          document.addEventListener('mouseover', (event) => {
            clearTimeout(hoverTimer);
            hoverTimer = setTimeout(() => this.handleHover(event), 500);
          });

          document.addEventListener('mouseout', () => {
            clearTimeout(hoverTimer);
          });
        }

        // Track element visibility
        if (config.trackVisibility && 'IntersectionObserver' in window) {
          this.setupVisibilityTracking();
        }

        // Track route changes for SPAs
        if (config.trackRouteChanges) {
          const originalPush = history.pushState;
          const originalReplace = history.replaceState;

          history.pushState = function() {
            originalPush.apply(this, arguments);
            analytics.trackPageview({ navigation_type: 'pushState' });
          };

          history.replaceState = function() {
            originalReplace.apply(this, arguments);
            analytics.trackPageview({ navigation_type: 'replaceState' });
          };

          window.addEventListener('popstate', () =>
            this.trackPageview({ navigation_type: 'popstate' })
          );
        }

        // Track page exit intent
        document.addEventListener('mouseleave', (event) => {
          if (event.clientY <= 0) {
            state.pageLeaveIntent++;
            this.track('exit_intent', {
              count: state.pageLeaveIntent,
              mouse_position: { x: event.clientX, y: event.clientY }
            });
          }
        });

        // Track errors
        if (config.trackErrors) {
          window.addEventListener('error', this.handleError.bind(this));
          window.addEventListener('unhandledrejection', this.handlePromiseRejection.bind(this));
        }

        // Track console logs
        if (config.trackConsole) {
          this.interceptConsole();
        }

        // Track media
        if (config.trackMedia) {
          this.setupMediaTracking();
        }

        // Track page exit
        window.addEventListener('beforeunload', () => {
          this.track('page_exit', {
            time_spent: Math.round((Date.now() - state.pageLoadTime) / 1000),
            scroll_depth: state.maxScrollDepth,
            interaction_count: state.interactionCount
          });
          this.flush(true);
        });
      },

      setupVisibilityTracking() {
        // Track elements that should be tracked for visibility
        const selectors = [
          'h1', 'h2', '.hero', '.cta', '.testimonial',
          '.pricing', '[data-sa-track-view]', 'button.primary',
          'section', 'article', '.product', '.feature'
        ];

        const elements = document.querySelectorAll(selectors.join(','));

        const observer = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              const element = entry.target;

              this.track('element_view', {
                element: utils.getElementAttributes(element),
                visible_time: new Date()
              });

              // Stop observing once seen
              observer.unobserve(element);
            }
          });
        }, { threshold: 0.5 });

        elements.forEach(element => observer.observe(element));
      },

      setupMediaTracking() {
        // Track video and audio elements
        const mediaElements = document.querySelectorAll('video, audio');

        mediaElements.forEach(media => {
          const mediaType = media.tagName.toLowerCase();
          const mediaUrl = media.currentSrc || media.src || 'unknown';
          const mediaId = media.id || 'unnamed_' + mediaType;

          media.addEventListener('play', () => {
            this.track('media_play', {
              media_type: mediaType,
              media_id: mediaId,
              media_url: mediaUrl,
              media_duration: media.duration,
              media_current_time: media.currentTime
            });
          });

          media.addEventListener('pause', () => {
            this.track('media_pause', {
              media_type: mediaType,
              media_id: mediaId,
              media_url: mediaUrl,
              media_duration: media.duration,
              media_current_time: media.currentTime,
              media_progress: media.duration ? Math.round((media.currentTime / media.duration) * 100) : 0
            });
          });

          media.addEventListener('ended', () => {
            this.track('media_complete', {
              media_type: mediaType,
              media_id: mediaId,
              media_url: mediaUrl,
              media_duration: media.duration
            });
          });
        });
      },

      interceptConsole() {
        // Only track errors and warnings
        const originalError = console.error;
        const originalWarn = console.warn;

        console.error = (...args) => {
          try {
            this.track('console_error', {
              message: String(args[0]),
              details: utils.getSafeObj(args)
            });
          } catch (e) {}
          originalError.apply(console, args);
        };

        console.warn = (...args) => {
          try {
            this.track('console_warn', {
              message: String(args[0]),
              details: utils.getSafeObj(args)
            });
          } catch (e) {}
          originalWarn.apply(console, args);
        };
      },

      handleClick(event) {
        if (!utils.shouldSample()) return;
        state.interactionCount++;

        const el = event.target.closest('[data-sa-track], a, button, [role="button"], input[type="submit"], input[type="button"], .btn, .button, .nav-item, .menu-item');
        if (!el) return;

        const position = {
          x: event.clientX,
          y: event.clientY,
          relative_x: Math.round((event.clientX / window.innerWidth) * 100),
          relative_y: Math.round((event.clientY / window.innerHeight) * 100)
        };

        this.track('click', {
          element: utils.getElementAttributes(el),
          position,
          page_position: utils.viewportInfo()
        });
      },

      handleForm(event) {
        state.interactionCount++;
        const form = event.target;
        if (!form || !form.tagName.match(/FORM/i)) return;

        // Collect form field data (without values, only structure)
        const fields = [];
        for (let i = 0; i < form.elements.length; i++) {
          const field = form.elements[i];
          if (!field.name || field.name === '') continue;

          fields.push({
            name: field.name,
            type: field.type || field.tagName.toLowerCase(),
            required: field.required,
            has_value: field.value !== '',
            length: (field.value || '').length
          });
        }

        this.track('form_submit', {
          form_id: form.id || null,
          form_name: form.name || null,
          form_action: form.action || null,
          form_method: form.method || 'GET',
          element_count: form.elements.length,
          fields: fields,
          has_validation: form.checkValidity && typeof form.checkValidity === 'function',
          is_valid: form.checkValidity ? form.checkValidity() : null
        });
      },

      handleInput(event) {
        if (!utils.shouldSample()) return;

        const input = event.target;
        if (!input || !input.name || !input.tagName.match(/INPUT|TEXTAREA|SELECT/)) return;

        state.interactionCount++;

        this.track('input_blur', {
          input: {
            name: input.name,
            id: input.id || null,
            type: input.type || input.tagName.toLowerCase(),
            required: input.required || false,
            has_value: input.value !== '',
            value_length: (input.value || '').length,
            valid: input.checkValidity ? input.checkValidity() : null,
            path: utils.getFullElementPath(input),
            form_id: input.form ? input.form.id : null,
            placeholder: input.placeholder || null
          }
        });
      },

      handleChange(event) {
        if (!utils.shouldSample()) return;

        const el = event.target;
        if (!el || !el.tagName) return;

        state.interactionCount++;

        // Handle checkbox/radio specifically
        if (el.type === 'checkbox' || el.type === 'radio') {
          this.track('input_change', {
            input: {
              name: el.name || null,
              id: el.id || null,
              type: el.type,
              checked: el.checked,
              value: el.value,
              path: utils.getFullElementPath(el),
              form_id: el.form ? el.form.id : null
            }
          });
        }
        // Handle select elements
        else if (el.tagName === 'SELECT') {
          this.track('input_change', {
            input: {
              name: el.name || null,
              id: el.id || null,
              type: 'select',
              selected_index: el.selectedIndex,
              selected_value: el.value,
              selected_text: el.options[el.selectedIndex] ? el.options[el.selectedIndex].text : null,
              option_count: el.options.length,
              path: utils.getFullElementPath(el),
              form_id: el.form ? el.form.id : null
            }
          });
        }
      },

      handleScroll(event) {
        if (!utils.shouldSample()) return;

        // Get scroll depth percentage
        const windowHeight = window.innerHeight;
        const documentHeight = Math.max(
          document.body.scrollHeight,
          document.body.offsetHeight,
          document.documentElement.clientHeight,
          document.documentElement.scrollHeight,
          document.documentElement.offsetHeight
        );
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const scrollPercentage = Math.round((scrollTop / (documentHeight - windowHeight)) * 100);

        // Only track significant changes in scroll depth
        if (scrollPercentage > state.maxScrollDepth) {
          // Track at 25%, 50%, 75%, 90%, and 100%
          const thresholds = [25, 50, 75, 90, 100];
          const previousThresholdReached = thresholds.filter(t => t <= state.maxScrollDepth);
          const newThresholdsReached = thresholds.filter(t => t > state.maxScrollDepth && t <= scrollPercentage);

          state.maxScrollDepth = scrollPercentage;

          if (newThresholdsReached.length > 0) {
            this.track('scroll_depth', {
              previous_depth: previousThresholdReached.length ? Math.max(...previousThresholdReached) : 0,
              current_depth: scrollPercentage,
              thresholds_reached: newThresholdsReached,
              scroll_position: scrollTop,
              document_height: documentHeight,
              viewport_height: windowHeight
            });
          }
        }
      },

      handleHover(event) {
        if (!utils.shouldSample()) return;

        const el = event.target.closest('a, button, [role="button"], .btn, .cta, [data-sa-track], .menu-item, .card, .product');
        if (!el) return;

        state.interactionCount++;

        this.track('hover', {
          element: utils.getElementAttributes(el),
          position: {
            x: event.clientX,
            y: event.clientY,
            relative_x: Math.round((event.clientX / window.innerWidth) * 100),
            relative_y: Math.round((event.clientY / window.innerHeight) * 100)
          }
        });
      },

      handleError(event) {
        this.track('js_error', {
          message: event.message || 'Unknown error',
          source: event.filename || event.currentTarget?.src || 'unknown',
          line: event.lineno,
          column: event.colno,
          stack: event.error?.stack || null,
          timestamp: utils.timestamp()
        });
      },

      handlePromiseRejection(event) {
        let message = 'Promise rejected';
        let stack = null;

        if (event.reason) {
          if (typeof event.reason === 'string') {
            message = event.reason;
          } else if (event.reason instanceof Error) {
            message = event.reason.message;
            stack = event.reason.stack;
          } else {
            message = String(event.reason);
          }
        }

        this.track('promise_error', {
          message,
          stack,
          timestamp: utils.timestamp()
        });
      },

      trackHeartbeat() {
        if (!utils.shouldSample()) return;

        const currentTime = new Date();
        const timeOnPage = Math.round((currentTime - state.pageLoadTime) / 1000);
        const idleTime = Math.round((currentTime - state.lastActivity) / 1000);

        // Only send heartbeat if user has been on page more than 30 seconds
        if (timeOnPage > 30) {
          this.track('heartbeat', {
            time_on_page: timeOnPage,
            idle_time: idleTime,
            scroll_depth: state.maxScrollDepth,
            interaction_count: state.interactionCount,
            viewport: utils.viewportInfo(),
            network_requests: state.networkRequests.length,
            timestamp: utils.timestamp()
          });
        }
      },

      trackPageview(extra = {}) {
        state.pageLoadTime = new Date();
        state.lastActivity = new Date();
        state.maxScrollDepth = 0;
        state.interactionCount = 0;

        // Get performance data if available
        let performance = {};
        if (config.trackPerformance) {
          performance = utils.getPerformanceMetrics();
        }

        // Extract UTM parameters from URL
        const urlParams = new URLSearchParams(window.location.search);
        const utmParams = {};
        ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'].forEach(param => {
          if (urlParams.has(param)) {
            utmParams[param] = urlParams.get(param);
          }
        });

        // Get page metadata
        const meta = {};
        document.querySelectorAll('meta').forEach(tag => {
          const name = tag.getAttribute('name') || tag.getAttribute('property');
          if (name) {
            meta[name] = tag.getAttribute('content');
          }
        });

        this.track('pageview', {
          path: window.location.pathname,
          url: window.location.href,
          title: document.title,
          referrer: document.referrer || null,
          utm: Object.keys(utmParams).length > 0 ? utmParams : null,
          performance,
          page_meta: meta,
          page_sequence: state.pageSequence,
          visit_count: state.visitCount,
          loaded_at: utils.timestamp(),
          ...extra
        });
      },

      track(name, properties = {}) {
        // Check if we should sample this event
        if (!utils.shouldSample() && name !== 'pageview' && name !== 'js_error') {
          return;
        }

        const browser = utils.browserInfo();
        const device = utils.deviceInfo();
        const session = utils.sessionInfo();

        const payload = {
          event_name: name,
          session_id: state.sessionId,
          user_id: state.userId,
          device_id: state.deviceId,
          timestamp: utils.timestamp(),
          url: window.location.href,
          pathname: window.location.pathname,
          page_title: document.title,
          ...utils.viewportInfo(),
          ...browser,
          ...device,
          ...session,
          properties
        };

        if (state.debugMode) {
          console.log(`[Candle] Track event: ${name}`, payload);
        }

        state.queue.push(payload);
        if (state.queue.length >= 5) this.flush();
      },

      flush(sync = false) {
        if (!state.queue.length) {
          if (state.debugMode) {
            console.debug('[Candle] No events to flush.');
          }
          return;
        }

        const queue = [...state.queue];
        state.queue = [];

        const body = JSON.stringify({
          api_key: config.apiKey,
          domain: config.domain,
          site_id: config.siteId,
          events: queue
        });

        if (state.debugMode) {
          console.debug('[Candle] Flushing events:', queue);
        }

        if (sync && navigator.sendBeacon) {
          const success = navigator.sendBeacon(config.endpoint, body);
          if (state.debugMode) {
            console.debug('[Candle] Sent with sendBeacon:', success);
          }
        } else {
          fetch(config.endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body,
            keepalive: true
          })
          .then(response => {
            if (state.debugMode) {
              console.debug('[Candle] Fetch response:', response.status);
            }
            if (!response.ok) {
              console.warn('[Candle] Failed to send events. Status:', response.status);
              state.queue = [...queue, ...state.queue];
            }
          })
          .catch(error => {
            console.error('[Candle] Error sending events:', error);
            state.queue = [...queue, ...state.queue];
          });
        }
      }
    };

    analytics.init();

    window.Candle = {
      track: analytics.track.bind(analytics),
      trackPageview: analytics.trackPageview.bind(analytics),
      flush: analytics.flush.bind(analytics),
      setUserId: (id) => {
        if (id && typeof id === 'string') {
          state.userId = id;
          utils.setCookie('sa_user_id', id, config.cookieTimeout || 30);
          console.log('[Candle] User ID set:', id);
        }
      },
      debug: (enable = true) => {
        state.debugMode = enable;
        console.log(`[Candle] Debug mode ${enable ? 'enabled' : 'disabled'}`);
      },
      getState: () => ({ ...state }),
      setProperty: (key, value) => {
        if (key && value !== undefined) {
          analytics.track('set_property', { [key]: value });
        }
      }
    };
  })();
