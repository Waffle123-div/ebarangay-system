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
    if (window.scrollY > scrollThreshold) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  };

  window.addEventListener('scroll', handleScroll, { passive: true });
  handleScroll();

  // ============================
  // Mobile menu toggle
  // ============================
  const menuToggle = document.getElementById('menuToggle');
  const navLinks = document.getElementById('navLinks');

  menuToggle.addEventListener('click', () => {
    menuToggle.classList.toggle('active');
    navLinks.classList.toggle('open');
    document.body.style.overflow = navLinks.classList.contains('open') ? 'hidden' : '';
  });

  // Close menu on link click
  navLinks.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
      menuToggle.classList.remove('active');
      navLinks.classList.remove('open');
      document.body.style.overflow = '';
    });
  });

  // ============================
  // Active nav link highlighting
  // ============================
  const sections = document.querySelectorAll('section[id]');
  const navLinksAll = navLinks.querySelectorAll('a');

  const highlightNavLink = () => {
    const scrollPos = window.scrollY + 200;

    sections.forEach(section => {
      const top = section.offsetTop;
      const height = section.offsetHeight;
      const id = section.getAttribute('id');

      if (scrollPos >= top && scrollPos < top + height) {
        navLinksAll.forEach(link => {
          link.classList.remove('active');
          if (link.getAttribute('href') === `#${id}`) {
            link.classList.add('active');
          }
        });
      }
    });
  };

  window.addEventListener('scroll', highlightNavLink, { passive: true });

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
  // Document Request Modal
  // ============================
  const modal = document.getElementById('requestModal');
  const modalTitle = document.getElementById('modalTitle');
  const modalSubtitle = document.getElementById('modalSubtitle');
  const modalClose = document.getElementById('modalClose');
  const cancelRequest = document.getElementById('cancelRequest');
  const requestForm = document.getElementById('requestForm');

  let selectedDocType = '';

  // Open modal from "Request Now" buttons
  document.querySelectorAll('.request-doc-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      selectedDocType = btn.dataset.doc;
      modalTitle.textContent = `Request: ${selectedDocType}`;
      modalSubtitle.textContent = `Fill out the form below to request a ${selectedDocType}`;
      modal.classList.add('open');
      document.body.style.overflow = 'hidden';
    });
  });

  // Close modal
  const closeModal = () => {
    modal.classList.remove('open');
    document.body.style.overflow = '';
    requestForm.reset();
  };

  modalClose.addEventListener('click', closeModal);
  cancelRequest.addEventListener('click', closeModal);

  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
  });

  // Handle form submission
  requestForm.addEventListener('submit', (e) => {
    e.preventDefault();

    // Show a warning toast telling the guest to log in or register
    showToast(
      'Authentication Required',
      'Please log in or sign up for an account first to request a document.',
      true // Triggers the yellow warning alert
    );

    // Close the modal so they see the warning clearly
    closeModal();
  });


  // ============================
  // Community Concern Form
  // ============================
  const concernForm = document.getElementById('concernForm');
  if (concernForm) {
    concernForm.addEventListener('submit', (e) => {
      e.preventDefault();
      
      // Show a warning toast telling the guest to log in or register
      showToast(
        'Authentication Required',
        'Please log in or sign up for an account first to submit a community concern.',
        true // Triggers the yellow warning alert
      );
    });
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
    rootMargin: '0px 0px -80px 0px',
    threshold: 0.1
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

  // ============================
  // Virtual Assistant Chat Widget
  // ============================
  const chatToggle = document.getElementById('chatToggle');
  const chatWindow = document.getElementById('chatWindow');
  const chatBody = document.getElementById('chatBody');
  const chatPromptBtns = document.getElementById('chatPromptBtns');

  // Toggle chat
  chatToggle.addEventListener('click', () => {
    chatToggle.classList.toggle('active');
    chatWindow.classList.toggle('open');
  });

  // E-Barangay specific bot responses
  const botResponses = {
    'how-to-request': `Here's how to request a document online:\n\n1️⃣ Register an account on E-Barangay\n2️⃣ Wait for barangay officials to verify your identity\n3️⃣ Log in and select the document you need\n4️⃣ Fill out the online request form\n5️⃣ Submit and get your tracking number\n6️⃣ Visit the Barangay Hall when your document is ready!\n\nYou can also scroll to the "Available Documents" section on this page to submit a request directly.`,

    'track': `To track your document request:\n\n🔍 Go to the "Track Request" section\n📋 Enter your tracking number (e.g., EB-2026-00142)\n📊 View your request status in real-time\n\nStatus stages:\n⏳ Pending → 🔄 Under Review → ✅ Approved → 📦 Ready for Pickup\n\nTry these sample tracking numbers:\n• EB-2026-00142 (Ready for Pickup)\n• EB-2026-00201 (Under Review)\n• EB-2026-00315 (Pending)`,

    'requirements': `Here are the common requirements:\n\n📄 Barangay Clearance:\n• Valid Government ID\n• 2x2 Photo\n• Proof of Residency\n• ₱100 fee\n\n🏠 Certificate of Residency:\n• Valid ID\n• Utility Bill or Lease Contract\n• ₱50 fee\n\n💰 Certificate of Indigency:\n• Valid ID\n• Brief Interview\n• FREE\n\n🏪 Business Clearance:\n• Valid ID, DTI Registration\n• ₱200 fee\n\n🪪 Barangay ID:\n• Valid ID, 1x1 Photo\n• ₱75 fee`,

    'schedule': `Barangay Cogon East Office Hours:\n\n🕐 Monday – Friday: 8:00 AM – 5:00 PM\n🕐 Saturday: 8:00 AM – 12:00 NN (limited services)\n❌ Sunday & Holidays: Closed\n\n📍 Location: Barangay Hall, Cogon East, Carmen, Cebu\n📞 Contact: (032) 555-0100\n✉️ Email: ebarangay.cogoneast@gmail.com\n\n💻 Online requests via E-Barangay are available 24/7!`,

    'concern': `To submit a community concern:\n\n1️⃣ Scroll down to the "Submit a Community Concern" section\n2️⃣ Enter your name and select the concern category\n3️⃣ Describe your concern in detail\n4️⃣ Click "Submit Concern"\n\nCategories available:\n🏗️ Infrastructure\n🛡️ Peace & Order\n🧹 Sanitation\n💡 Utilities\n\nBarangay officials will review and respond to your concern.`
  };

  // Handle prompt button clicks
  chatPromptBtns.addEventListener('click', (e) => {
    const btn = e.target.closest('.chat-prompt-btn');
    if (!btn) return;

    const promptKey = btn.dataset.prompt;
    const userText = btn.textContent.trim();

    // Add user message
    const userMsg = document.createElement('div');
    userMsg.className = 'chat-msg user';
    userMsg.textContent = userText;
    chatBody.appendChild(userMsg);

    // Scroll to bottom
    chatBody.scrollTop = chatBody.scrollHeight;

    // Simulate typing delay
    setTimeout(() => {
      const botMsg = document.createElement('div');
      botMsg.className = 'chat-msg bot';
      botMsg.textContent = botResponses[promptKey] || "I'm sorry, I don't have information about that yet. Please visit the Barangay Hall or call (032) 555-0100 for assistance.";
      chatBody.appendChild(botMsg);
      chatBody.scrollTop = chatBody.scrollHeight;
    }, 600);
  });

  // ============================
  // Smooth scroll for all anchor links
  // ============================
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const targetId = this.getAttribute('href');
      if (targetId === '#') return;

      const targetEl = document.querySelector(targetId);
      if (targetEl) {
        e.preventDefault();
        targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

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

});