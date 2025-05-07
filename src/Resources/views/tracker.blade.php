/**
 * Candle Tracker - Heap-style
 * Automatically captures user behavior on websites
 */
(function() {
    const config = {
      endpoint: '{{ route("candle.collect") }}',
      siteId: '{{ $siteId }}',
      apiKey: '{{ $apiKey }}',
      trackClicks: {{ $trackClicks ? 'true' : 'false' }},
      trackForms: {{ $trackForms ? 'true' : 'false' }},
      trackRouteChanges: {{ $trackRouteChanges ? 'true' : 'false' }},
      cookieTimeout: {{ $cookieTimeout }},
      domain: window.location.hostname
    };

    const state = {
      sessionId: null,
      userId: null,
      queue: [],
      flushTimer: null,
      pageLoadTime: new Date()
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
        width: screen.width,
        height: screen.height
      }),
      lang: () => navigator.language || navigator.userLanguage
    };

    const analytics = {
      init() {
        state.sessionId = utils.getCookie('sa_session_id') || utils.uuid();
        utils.setCookie('sa_session_id', state.sessionId, 1);

        state.userId = utils.getCookie('sa_user_id') || utils.uuid();
        utils.setCookie('sa_user_id', state.userId, config.cookieTimeout || 30);

        this.trackPageview();
        this.bindEvents();
        state.flushTimer = setInterval(() => this.flush(), 10_000);
      },

      bindEvents() {
        if (config.trackClicks) {
          document.addEventListener('click', this.handleClick.bind(this));
        }

        if (config.trackForms) {
          document.addEventListener('submit', this.handleForm.bind(this));
          document.addEventListener('focusout', this.handleInput.bind(this), true);
        }

        if (config.trackRouteChanges) {
          const originalPush = history.pushState;
          const originalReplace = history.replaceState;

          history.pushState = function() {
            originalPush.apply(this, arguments);
            analytics.trackPageview();
          };

          history.replaceState = function() {
            originalReplace.apply(this, arguments);
            analytics.trackPageview();
          };

          window.addEventListener('popstate', () => this.trackPageview());
        }

        window.addEventListener('beforeunload', () => {
          this.track('page_exit', {
            time_spent: Math.round((Date.now() - state.pageLoadTime) / 1000)
          });
          this.flush(true);
        });
      },

      handleClick(event) {
        const el = event.target.closest('[data-sa-track], a, button, [role="button"]');

        if (!el) return;

        const eventData = {
          tag: el.tagName.toLowerCase(),
          id: el.id || null,
          class: el.className || null,
          text: (el.innerText || '').trim().substring(0, 100),
          href: el.getAttribute('href') || null,
          role: el.getAttribute('role') || null,
          dataset: { ...el.dataset }
        };

        this.track('click', eventData);
      },

      handleForm(event) {
        const form = event.target;
        if (!form || !form.tagName.match(/FORM/i)) return;

        this.track('form_submit', {
          id: form.id || null,
          action: form.action || null,
          method: form.method || 'GET',
          elements: form.elements.length
        });
      },

      handleInput(event) {
        const input = event.target;
        if (!input || !input.name || !input.tagName.match(/INPUT|TEXTAREA|SELECT/)) return;

        this.track('input_blur', {
          name: input.name,
          type: input.type || input.tagName.toLowerCase(),
          value_length: (input.value || '').length
        });
      },

      trackPageview(extra = {}) {
        state.pageLoadTime = new Date();

        this.track('pageview', {
          path: window.location.pathname,
          url: window.location.href,
          title: document.title,
          referrer: document.referrer,
          ...extra
        });
      },

      track(name, properties = {}) {
        const payload = {
          event_name: name,
          session_id: state.sessionId,
          user_id: state.userId,
          timestamp: utils.timestamp(),
          url: window.location.href,
          language: utils.lang(),
          ...utils.screenSize(),
          properties
        };

        state.queue.push(payload);
        if (state.queue.length >= 5) this.flush();
      },

      flush(sync = false) {
        if (!state.queue.length) {
          console.debug('[Candle] No events to flush.');
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

        console.debug('[Candle] Flushing events:', queue);

        if (sync && navigator.sendBeacon) {
          const success = navigator.sendBeacon(config.endpoint, body);
          console.debug('[Candle] Sent with sendBeacon:', success);
        } else {
            console.log(config.endpoint);
          fetch(config.endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body,
            keepalive: true
          })
          .then(response => {
            console.debug('[Candle] Fetch response:', response.status);
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
      trackPageview: analytics.trackPageview.bind(analytics)
    };
  })();
