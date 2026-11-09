/* ========================================
   E-BARANGAY: Web-Based Document Request
   & Tracking System — Interactive JavaScript
   Barangay Cogon East, Carmen, Cebu
   ======================================== */

document.addEventListener('DOMContentLoaded', () => {

  // ============================
  // Header scroll effect
  // ============================
  const header = document.getElementById('header');
  const scrollThreshold = 60;

  const handleScroll = () => {
    if (!header) return;
    if (window.scrollY > scrollThreshold) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  };

  // ============================
  // Mobile menu toggle
  // ============================
  const menuToggle = document.getElementById('menuToggle');
  const navLinks = document.getElementById('navLinks');

  if (menuToggle && navLinks) {
    menuToggle.addEventListener('click', () => {
      menuToggle.classList.toggle('active');
      navLinks.classList.toggle('open');
      const isOpen = navLinks.classList.contains('open');
      document.body.style.overflow = isOpen ? 'hidden' : '';
      document.body.classList.toggle('mobile-menu-open', isOpen);
    });

    // Close menu on link click
    navLinks.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        menuToggle.classList.remove('active');
        navLinks.classList.remove('open');
        document.body.style.overflow = '';
        document.body.classList.remove('mobile-menu-open');
        setTimeout(() => {
          if (typeof revealVisibleFadeElements === 'function') {
            revealVisibleFadeElements();
          }
        }, 150);
      });
    });
  }

  // ============================
  // Active nav link highlighting
  // ============================
  const sections = document.querySelectorAll('section[id]');
  const navLinksAll = navLinks ? navLinks.querySelectorAll('a') : [];

  // Pre-calculate section offsets to avoid layout thrashing during scroll
  let cachedSectionOffsets = [];
  const updateSectionOffsets = () => {
    cachedSectionOffsets = Array.from(sections).map(section => ({
      id: section.getAttribute('id'),
      top: section.offsetTop,
      height: section.offsetHeight
    }));
  };
  updateSectionOffsets();
  window.addEventListener('resize', updateSectionOffsets, { passive: true });

  const highlightNavLink = () => {
    if (!navLinksAll.length || !cachedSectionOffsets.length) return;
    const scrollPos = window.scrollY + 200;

    for (let i = 0; i < cachedSectionOffsets.length; i++) {
      const sec = cachedSectionOffsets[i];
      if (scrollPos >= sec.top && scrollPos < sec.top + sec.height) {
        navLinksAll.forEach(link => {
          link.classList.toggle('active', link.getAttribute('href') === `#${sec.id}`);
        });
        break;
      }
    }
  };

  // Unified high-performance scroll handler (at most 1 tick per animation frame)
  let scrollTicking = false;
  const onUnifiedScroll = () => {
    if (!scrollTicking) {
      requestAnimationFrame(() => {
        handleScroll();
        highlightNavLink();
        scrollTicking = false;
      });
      scrollTicking = true;
    }
  };

  window.addEventListener('scroll', onUnifiedScroll, { passive: true });
  handleScroll();

  // ============================
  // Services Search/Filter
  // ============================
  const searchInput = document.getElementById('serviceSearchInput');
  const servicesGrid = document.getElementById('servicesGrid');
  const serviceCards = servicesGrid.querySelectorAll('.service-card');
  const noResults = document.getElementById('servicesNoResults');

  searchInput.addEventListener('input', () => {
    const query = searchInput.value.toLowerCase().trim();
    let visibleCount = 0;

    serviceCards.forEach(card => {
      const keywords = card.dataset.keywords || '';
      const title = card.querySelector('h3').textContent.toLowerCase();
      const desc = card.querySelector('p').textContent.toLowerCase();
      const searchText = `${keywords} ${title} ${desc}`;

      const isMatch = !query || searchText.includes(query);
      card.style.display = isMatch ? '' : 'none';
      if (isMatch) visibleCount++;
    });

    noResults.classList.toggle('visible', visibleCount === 0);
  });

  // ============================
  // Auth helpers (Welcome Page)
  // ============================
  const bodyEl = document.body;
  const isResident = bodyEl?.dataset.isResident === '1';
  const loginUrl = bodyEl?.dataset.loginUrl || '/resident/login';
  const requestUrl = bodyEl?.dataset.requestUrl || '/resident/request-document';
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');

  const authRequiredModal = document.getElementById('authRequiredModal');
  const authRequiredTitle = document.getElementById('authRequiredTitle');
  const authRequiredMessage = document.getElementById('authRequiredMessage');
  const authRequiredDescription = document.getElementById('authRequiredDescription');
  const authRequiredClose = document.getElementById('authRequiredClose');
  const authRequiredCancel = document.getElementById('authRequiredCancel');
  const authRequiredLogin = document.getElementById('authRequiredLogin');
  const residentLoginModal = document.getElementById('residentLoginModal');
  const residentLoginClose = document.getElementById('residentLoginClose');
  const welcomeLoginForm = document.getElementById('welcomeResidentLoginForm');
  const welcomeLoginError = document.getElementById('welcomeLoginError');
  const welcomeGoogleLogin = document.getElementById('welcomeGoogleLogin');
  let pendingAfterLoginUrl = '';
  let welcomeRecaptchaWidgetId = null;

  window.renderWelcomeRecaptcha = function () {
    const el = document.getElementById('welcomeResidentLoginFormRecaptcha');
    if (!el || typeof grecaptcha === 'undefined' || typeof grecaptcha.render !== 'function') {
      return;
    }

    try {
      if (welcomeRecaptchaWidgetId === null) {
        welcomeRecaptchaWidgetId = grecaptcha.render(el, { sitekey: el.dataset.sitekey });
      } else {
        grecaptcha.reset(welcomeRecaptchaWidgetId);
      }
    } catch (err) {
      // Widget may already be rendered; reset instead.
      if (welcomeRecaptchaWidgetId !== null) {
        grecaptcha.reset(welcomeRecaptchaWidgetId);
      }
    }
  };

  const toRelativeUrl = (url) => {
    if (!url) return '';
    try {
      const parsed = new URL(url, window.location.origin);
      return `${parsed.pathname}${parsed.search}`;
    } catch {
      return url;
    }
  };

  const openAuthRequiredModal = (title, message, description, afterLoginUrl = '') => {
    if (!authRequiredModal) return;

    pendingAfterLoginUrl = afterLoginUrl || '';
    if (authRequiredTitle) authRequiredTitle.textContent = title;
    if (authRequiredMessage) authRequiredMessage.textContent = message;
    if (authRequiredDescription) authRequiredDescription.textContent = description;

    authRequiredModal.classList.add('open');
    document.body.style.overflow = 'hidden';
  };

  const closeAuthRequiredModal = () => {
    if (!authRequiredModal) return;
    authRequiredModal.classList.remove('open');
    if (!residentLoginModal?.classList.contains('open')) {
      document.body.style.overflow = '';
    }
  };

  const openResidentLoginModal = () => {
    if (!residentLoginModal) return;

    const intendedInput = document.getElementById('welcomeResidentLoginFormIntended');
    const relativeIntended = toRelativeUrl(pendingAfterLoginUrl);
    if (intendedInput) intendedInput.value = relativeIntended;

    if (welcomeGoogleLogin) {
      const googleBase = welcomeGoogleLogin.getAttribute('href')?.split('?')[0] || '/auth/google';
      const googleUrl = new URL(googleBase, window.location.origin);
      googleUrl.searchParams.set('action', 'login');
      if (relativeIntended) googleUrl.searchParams.set('intended', relativeIntended);
      welcomeGoogleLogin.href = `${googleUrl.pathname}${googleUrl.search}`;
    }

    if (welcomeLoginError) {
      welcomeLoginError.hidden = true;
      welcomeLoginError.textContent = '';
    }

    residentLoginModal.classList.add('open');
    document.body.style.overflow = 'hidden';
    document.getElementById('welcomeResidentLoginFormEmail')?.focus();

    if (window.welcomeRecaptchaReady) {
      window.renderWelcomeRecaptcha();
    }
  };

  const closeResidentLoginModal = () => {
    if (!residentLoginModal) return;
    residentLoginModal.classList.remove('open');
    document.body.style.overflow = '';
  };

  if (authRequiredClose) authRequiredClose.addEventListener('click', closeAuthRequiredModal);
  if (authRequiredCancel) authRequiredCancel.addEventListener('click', closeAuthRequiredModal);
  if (authRequiredModal) {
    authRequiredModal.addEventListener('click', (e) => {
      if (e.target === authRequiredModal) closeAuthRequiredModal();
    });
  }
  if (authRequiredLogin) {
    authRequiredLogin.addEventListener('click', (e) => {
      e.preventDefault();
      closeAuthRequiredModal();
      openResidentLoginModal();
    });
  }
  if (residentLoginClose) residentLoginClose.addEventListener('click', closeResidentLoginModal);
  if (residentLoginModal) {
    residentLoginModal.addEventListener('click', (e) => {
      if (e.target === residentLoginModal) closeResidentLoginModal();
    });
  }

  if (welcomeLoginForm) {
    welcomeLoginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const submitBtn = document.getElementById('welcomeResidentLoginFormSubmit');
      if (submitBtn) submitBtn.disabled = true;

      if (welcomeLoginError) {
        welcomeLoginError.hidden = true;
        welcomeLoginError.textContent = '';
      }

      try {
        const response = await fetch(welcomeLoginForm.action, {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfMeta?.getAttribute('content') || '',
          },
          body: new FormData(welcomeLoginForm),
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
          const firstError = data.errors ? Object.values(data.errors).flat()[0] : null;
          const message = firstError || data.message || 'Invalid email or password.';
          if (welcomeLoginError) {
            welcomeLoginError.textContent = message;
            welcomeLoginError.hidden = false;
          }
          if (submitBtn) submitBtn.disabled = false;
          if (welcomeRecaptchaWidgetId !== null && typeof grecaptcha !== 'undefined') {
            grecaptcha.reset(welcomeRecaptchaWidgetId);
          }
          return;
        }

        window.location.href = data.redirect || pendingAfterLoginUrl || requestUrl;
      } catch {
        if (welcomeLoginError) {
          welcomeLoginError.textContent = 'Unable to log in right now. Please try again.';
          welcomeLoginError.hidden = false;
        }
        if (submitBtn) submitBtn.disabled = false;
      }
    });
  }

  // ============================
  // Document Request (auth-gated)
  // ============================
  const modal = document.getElementById('requestModal');
  const modalClose = document.getElementById('modalClose');
  const cancelRequest = document.getElementById('cancelRequest');
  const requestForm = document.getElementById('requestForm');

  const closeRequestModal = () => {
    if (!modal) return;
    modal.classList.remove('open');
    document.body.style.overflow = '';
    if (requestForm) requestForm.reset();
  };

  const continueToRequestDocument = (docType = '') => {
    return docType
      ? `${requestUrl}?document_type=${encodeURIComponent(docType)}`
      : requestUrl;
  };

  document.querySelectorAll('.request-doc-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopImmediatePropagation();

      const selectedDocType = btn.dataset.doc || '';
      const targetUrl = continueToRequestDocument(selectedDocType);

      if (!isResident) {
        pendingAfterLoginUrl = targetUrl;
        openResidentLoginModal();
        return;
      }

      window.location.href = targetUrl;
    });
  });

  if (modal && requestForm) {
    if (modalClose) modalClose.addEventListener('click', closeRequestModal);
    if (cancelRequest) cancelRequest.addEventListener('click', closeRequestModal);

    modal.addEventListener('click', (e) => {
      if (e.target === modal) closeRequestModal();
    });

    requestForm.addEventListener('submit', (e) => {
      e.preventDefault();

      if (!isResident) {
        closeRequestModal();
        pendingAfterLoginUrl = requestUrl;
        openResidentLoginModal();
        return;
      }

      window.location.href = requestUrl;
    });
  }

  // ============================
  // Community Concern Form
  // ============================
  const concernForm = document.getElementById('concernForm');
  if (concernForm && concernForm.dataset.requireAuth === 'true') {
    concernForm.addEventListener('submit', (e) => {
      e.preventDefault();
      openAuthRequiredModal(
        'Login Required',
        'Please log in to submit a community concern.',
        'You need to log in first before submitting a community concern.',
        '/home'
      );
    });

    concernForm.querySelectorAll('input, select, textarea').forEach((field) => {
      field.addEventListener('keydown', (e) => e.preventDefault());
      field.addEventListener('paste', (e) => e.preventDefault());
    });
  }

  // ============================
  // Smooth scroll with header offset
  // ============================
  const scrollToSection = (targetEl) => {
    if (!targetEl) return;
    const headerOffset = header ? header.offsetHeight + 16 : 96;
    const targetTop = targetEl.getBoundingClientRect().top + window.scrollY - headerOffset;
    window.scrollTo({ top: targetTop, behavior: 'smooth' });
  };

  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      if (this.classList.contains('request-doc-btn')) return;

      const targetId = this.getAttribute('href');
      if (targetId === '#') return;

      const targetEl = document.querySelector(targetId);
      if (targetEl) {
        e.preventDefault();
        scrollToSection(targetEl);
      }
    });
  });

  if (window.location.hash) {
    const hashTarget = document.querySelector(window.location.hash);
    if (hashTarget) {
      setTimeout(() => scrollToSection(hashTarget), 150);
    }
  }

  // ============================
  // Toast Notification System
  // ============================
  const toast = document.getElementById('toast');
  const toastTitle = document.getElementById('toastTitle');
  const toastMessage = document.getElementById('toastMessage');
  const toastClose = document.getElementById('toastClose');

  let toastTimeout;

  function showToast(title, message, isWarning = false) {
    toastTitle.textContent = title;
    toastMessage.textContent = message;

    const toastIcon = toast.querySelector('.toast-icon');
    if (isWarning) {
      toastIcon.style.background = '#FEF3C7';
      toastIcon.style.color = '#D97706';
      toastIcon.innerHTML = '<i class="icon-alert-triangle"></i>';
    } else {
      toastIcon.style.background = '#D1FAE5';
      toastIcon.style.color = '#059669';
      toastIcon.innerHTML = '<i class="icon-check-circle"></i>';
    }

    toast.classList.add('visible');

    clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => {
      toast.classList.remove('visible');
    }, 5000);
  }

  toastClose.addEventListener('click', () => {
    toast.classList.remove('visible');
    clearTimeout(toastTimeout);
  });

  // ============================
  // Scroll-triggered fade-in animations
  // ============================
  const fadeElements = document.querySelectorAll('.fade-in-up');

  const observerOptions = {
    root: null,
    rootMargin: '0px 0px 50px 0px',
    threshold: 0.01
  };

  const fadeObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        fadeObserver.unobserve(entry.target);
      }
    });
  }, observerOptions);

  fadeElements.forEach(el => fadeObserver.observe(el));

  // Reveal elements immediately if they are in view (initial load or resize)
  function revealVisibleFadeElements() {
    const vh = window.innerHeight || document.documentElement.clientHeight;
    fadeElements.forEach(el => {
      const rect = el.getBoundingClientRect();
      if (rect.top <= vh + 60 && rect.bottom >= 0) {
        el.classList.add('visible');
      }
    });
  }

  revealVisibleFadeElements();
  let resizeFadeTimer = null;
  window.addEventListener('resize', () => {
    clearTimeout(resizeFadeTimer);
    resizeFadeTimer = setTimeout(revealVisibleFadeElements, 150);
  }, { passive: true });
  // NOTE: Redundant scroll listener removed — IntersectionObserver natively observes all elements without forced reflow!

  // ============================
  // ISTONG AI CHAT WIDGET
  // ============================
  const chatWidget = document.getElementById('chatWidget');
  const chatToggle = document.getElementById('chatToggle');
  const chatWindow = document.getElementById('chatWindow');
  const chatBody = document.getElementById('chatBody');
  const chatPromptBtns = document.getElementById('chatPromptBtns');
  const chatForm = document.getElementById('chatForm');
  const chatInput = document.getElementById('chatInput');
  const chatSendBtn = document.getElementById('chatSendBtn');
  const chatResetBtn = document.getElementById('chatResetBtn');
  const chatCloseBtn = document.getElementById('chatCloseBtn');
  const chatPill = document.getElementById('chatPill');

  let istongHistory = [];
  let isSending = false;

  const initialIstongGreeting = `Maayong adlaw, silingan! 👋 Ako diay si <strong>Istong</strong>, ang opisyal nga digital assistant sa Barangay Cogon East.
<br><br>
Unsay ako ikatabang nimo karon? Pwede ka mangutana bahin sa:
<br>&bull; Pamaagi sa pag-request og dokumento
<br>&bull; Requirements ug mga bayronon (fees)
<br>&bull; Pag-track sa status sa imong dokumento
<br>&bull; Oras sa opisina ug serbisyo sa barangay
<br><br>
<em>Pangutana lang bisan Bisaya o English!</em>`;

  // Toggle open/close
  if (chatToggle && chatWindow) {
    chatToggle.addEventListener('click', () => {
      const isOpen = chatWindow.classList.toggle('open');
      chatToggle.classList.toggle('active', isOpen);
      if (isOpen && chatInput) {
        setTimeout(() => chatInput.focus(), 200);
      }
    });
  }

  // Header close button
  if (chatCloseBtn && chatWindow && chatToggle) {
    chatCloseBtn.addEventListener('click', () => {
      chatWindow.classList.remove('open');
      chatToggle.classList.remove('active');
    });
  }

  // Floating pill click
  if (chatPill && chatWindow && chatToggle) {
    chatPill.addEventListener('click', () => {
      chatWindow.classList.add('open');
      chatToggle.classList.add('active');
      if (chatInput) setTimeout(() => chatInput.focus(), 200);
    });
  }

  // Reset / Clear chat
  if (chatResetBtn && chatBody) {
    chatResetBtn.addEventListener('click', () => {
      istongHistory = [];
      chatBody.innerHTML = `<div class="chat-msg bot">${initialIstongGreeting}</div>`;
      scrollChatToBottom();
      if (chatInput) chatInput.focus();
    });
  }

  function scrollChatToBottom() {
    if (chatBody) {
      chatBody.scrollTop = chatBody.scrollHeight;
    }
  }

  function formatMarkdownText(text) {
    if (!text) return '';
    // HTML Escape
    let escaped = text
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');

    // Bold
    escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

    // Italic
    escaped = escaped.replace(/\*([^\*]+)\*/g, '<em>$1</em>');

    // Numbered list items
    escaped = escaped.replace(/^(\d+)\.\s+(.+)$/gm, '<li class="chat-list-num"><span class="chat-num-badge">$1</span> <span>$2</span></li>');

    // Bullet items
    escaped = escaped.replace(/^[•\-\*]\s+(.+)$/gm, '<li class="chat-list-item">&bull; $1</li>');

    // Wrap list blocks
    escaped = escaped.replace(/((?:<li class="chat-list-[^"]+">.*?<\/li>\s*)+)/g, '<ul class="chat-list">$1</ul>');

    // Convert newlines outside tags
    escaped = escaped.replace(/\n{2,}/g, '<br><br>').replace(/\n/g, '<br>');

    return escaped;
  }

  function appendChatUserMessage(text) {
    if (!chatBody) return;
    const msg = document.createElement('div');
    msg.className = 'chat-msg user';
    msg.textContent = text;
    chatBody.appendChild(msg);
    scrollChatToBottom();
  }

  function appendChatBotMessage(html) {
    if (!chatBody) return;
    const msg = document.createElement('div');
    msg.className = 'chat-msg bot';
    msg.innerHTML = formatMarkdownText(html);
    chatBody.appendChild(msg);
    scrollChatToBottom();
  }

  function showChatTyping() {
    if (!chatBody) return;
    const typing = document.createElement('div');
    typing.className = 'chat-msg bot typing-msg';
    typing.id = 'chatTyping';
    typing.innerHTML = `
      <div class="typing-bubble">
        <span></span><span></span><span></span>
      </div>
      <small class="typing-label">Naghunahuna si Istong...</small>
    `;
    chatBody.appendChild(typing);
    scrollChatToBottom();
  }

  function hideChatTyping() {
    const typing = document.getElementById('chatTyping');
    if (typing) typing.remove();
  }

  async function handleSendToIstong(rawText) {
    const text = (rawText || '').trim();
    if (!text || isSending) return;

    isSending = true;
    if (chatInput) {
      chatInput.value = '';
      chatInput.disabled = true;
    }
    if (chatSendBtn) {
      chatSendBtn.disabled = true;
    }

    appendChatUserMessage(text);
    showChatTyping();

    // Push user message into conversation history
    istongHistory.push({ role: 'user', content: text });

    try {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const response = await fetch('/chatbot/message', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ history: istongHistory })
      });

      hideChatTyping();

      if (response.ok) {
        const data = await response.json();
        const replyText = data.reply || 'Pasensya silingan, wala koy tubag niana karon. Palihug sulayi og balik.';
        istongHistory.push({ role: 'assistant', content: replyText });
        appendChatBotMessage(replyText);
      } else {
        istongHistory.pop(); // remove failed user message from context
        appendChatBotMessage('Pasensya silingan, medyo busy o naglisod ang atong server karon. Palihug sulayi pag-usab taud-taud.');
      }
    } catch (err) {
      console.error('Istong AI fetch error:', err);
      hideChatTyping();
      istongHistory.pop();
      appendChatBotMessage('Pasensya silingan, naputol ang atong koneksyon sa internet. Palihug susiha ang imong koneksyon ug sulayi pag-usab.');
    } finally {
      isSending = false;
      if (chatInput) {
        chatInput.disabled = false;
        chatInput.focus();
      }
      if (chatSendBtn) {
        chatSendBtn.disabled = false;
      }
    }
  }

  // Chat Form submission
  if (chatForm && chatInput) {
    chatForm.addEventListener('submit', (e) => {
      e.preventDefault();
      handleSendToIstong(chatInput.value);
    });
  }

  // Quick Prompt chips
  if (chatPromptBtns) {
    chatPromptBtns.addEventListener('click', (e) => {
      const btn = e.target.closest('.chat-prompt-btn');
      if (!btn) return;
      const query = btn.dataset.query || btn.textContent.replace(/^[\uD800-\uDBFF\uDC00-\uDFFF\s]+/g, '').trim();
      handleSendToIstong(query);
    });
  }

  // ============================
  // Animated counter for hero stats
  // ============================
  const animateCounters = () => {
    const statValues = document.querySelectorAll('.hero-stat-value');
    
    statValues.forEach(stat => {
      const text = stat.textContent.trim();
      const numMatch = text.match(/[\d,]+/);
      if (!numMatch) return;

      const target = parseInt(numMatch[0].replace(/,/g, ''));
      const suffix = text.replace(numMatch[0], '');
      const duration = 2000;
      const startTime = performance.now();

      const updateCounter = (currentTime) => {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Ease-out cubic
        const eased = 1 - Math.pow(1 - progress, 3);
        const current = Math.floor(eased * target);

        stat.textContent = current.toLocaleString() + suffix;

        if (progress < 1) {
          requestAnimationFrame(updateCounter);
        }
      };

      requestAnimationFrame(updateCounter);
    });
  };

  // Trigger counter animation when hero is visible
  const heroObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        animateCounters();
        heroObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.3 });

  const heroSection = document.getElementById('hero');
  if (heroSection) {
    heroObserver.observe(heroSection);

    // Hero interactive background mouse tracking with smooth lerp
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const isTouchDevice = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0) || window.innerWidth < 768;

    if (!prefersReducedMotion && !isTouchDevice) {
      let targetX = 50, targetY = 45;
      let currentX = 50, currentY = 45;
      let isMoving = false;
      let heroRaf = null;
      let heroRect = null;

      const updateHeroRect = () => {
        if (heroSection) heroRect = heroSection.getBoundingClientRect();
      };

      const updateHeroPos = () => {
        currentX += (targetX - currentX) * 0.08;
        currentY += (targetY - currentY) * 0.08;

        heroSection.style.setProperty('--hero-mouse-x', `${currentX.toFixed(2)}%`);
        heroSection.style.setProperty('--hero-mouse-y', `${currentY.toFixed(2)}%`);

        // Tilt factor normalized from -1 to 1
        const tiltX = ((currentX - 50) / 50).toFixed(3);
        const tiltY = ((currentY - 50) / 50).toFixed(3);
        heroSection.style.setProperty('--hero-tilt-x', tiltX);
        heroSection.style.setProperty('--hero-tilt-y', tiltY);

        if (Math.abs(targetX - currentX) > 0.05 || Math.abs(targetY - currentY) > 0.05) {
          heroRaf = requestAnimationFrame(updateHeroPos);
        } else {
          isMoving = false;
        }
      };

      heroSection.addEventListener('mouseenter', updateHeroRect, { passive: true });
      window.addEventListener('resize', updateHeroRect, { passive: true });

      heroSection.addEventListener('mousemove', (e) => {
        if (!heroRect) updateHeroRect();
        targetX = ((e.clientX - heroRect.left) / heroRect.width) * 100;
        targetY = ((e.clientY - heroRect.top) / heroRect.height) * 100;
        if (!isMoving) {
          isMoving = true;
          heroRaf = requestAnimationFrame(updateHeroPos);
        }
      }, { passive: true });

      heroSection.addEventListener('mouseleave', () => {
        targetX = 50;
        targetY = 45;
        if (!isMoving) {
          isMoving = true;
          heroRaf = requestAnimationFrame(updateHeroPos);
        }
      });
    }
  }

  // ============================
  // Dark Mode Toggle
  // ============================
  const darkModeToggle = document.getElementById('darkModeToggle');
  const htmlEl = document.documentElement;

  // Restore saved preference on page load
  if (localStorage.getItem('ebarangay_dark_mode') === 'dark') {
    htmlEl.setAttribute('data-theme', 'dark');
  }

  darkModeToggle.addEventListener('click', () => {
    const isDark = htmlEl.getAttribute('data-theme') === 'dark';
    if (isDark) {
      htmlEl.removeAttribute('data-theme');
      localStorage.setItem('ebarangay_dark_mode', 'light');
    } else {
      htmlEl.setAttribute('data-theme', 'dark');
      localStorage.setItem('ebarangay_dark_mode', 'dark');
    }
  });

  // ============================
  // Welcome page — authenticated resident nav
  // ============================
  const welcomeNotifWrap = document.getElementById('welcomeNotifWrap');
  const welcomeNotifBtn = document.getElementById('welcomeNotifBtn');
  const welcomeProfileWrap = document.getElementById('welcomeProfileWrap');
  const welcomeProfileBtn = document.getElementById('welcomeProfileBtn');
  const welcomeNotifBadge = document.getElementById('welcomeNotifBadge');

  const closeWelcomeMenus = () => {
    welcomeNotifWrap?.classList.remove('open');
    welcomeProfileWrap?.classList.remove('open');
  };

  welcomeNotifBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    const willOpen = !welcomeNotifWrap.classList.contains('open');
    closeWelcomeMenus();
    if (willOpen) {
      welcomeNotifWrap.classList.add('open');
      if (welcomeNotifBadge && csrfMeta) {
        welcomeNotifBadge.remove();
        fetch('/resident/notifications/mark-read', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfMeta.getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
        }).catch(() => {});
      }
    }
  });

  welcomeProfileBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    const willOpen = !welcomeProfileWrap.classList.contains('open');
    closeWelcomeMenus();
    if (willOpen) {
      welcomeProfileWrap.classList.add('open');
    }
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('#welcomeNotifWrap') && !e.target.closest('#welcomeProfileWrap')) {
      closeWelcomeMenus();
    }
  });

});