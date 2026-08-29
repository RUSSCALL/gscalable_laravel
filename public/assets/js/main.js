/**
 * Share a job. Title and URL are passed in by the page — this file is static
 * JS and never sees Blade, so it cannot read the job itself.
 */
function shareJob(title, url) {
  const jobUrl = url || window.location.href;
  const jobTitle = (title || document.title) + ' at Global Scalable Technologies (GST)';

  if (navigator.share) {
    navigator.share({
      title: jobTitle,
      text: 'Check out this job opportunity: ' + jobTitle,
      url: jobUrl,
    }).catch(function () {
      /* The user dismissed the share sheet — nothing to do. */
    });
    return;
  }

  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(jobUrl).then(function () {
      alert('Job link copied to clipboard.');
    }).catch(function () {
      window.prompt('Copy this link to share the role:', jobUrl);
    });
    return;
  }

  window.prompt('Copy this link to share the role:', jobUrl);
}



(function() {
  "use strict";

  /**
   * Easy selector helper function
   */
  const select = (el, all = false) => {
    el = el.trim()
    if (all) {
      return [...document.querySelectorAll(el)]
    } else {
      return document.querySelector(el)
    }
  }

  /**
   * Easy event listener function
   */
  const on = (type, el, listener, all = false) => {
    let selectEl = select(el, all)
    if (selectEl) {
      if (all) {
        selectEl.forEach(e => e.addEventListener(type, listener))
      } else {
        selectEl.addEventListener(type, listener)
      }
    }
  }

  /**
   * Easy on scroll event listener 
   */
  const onscroll = (el, listener) => {
    el.addEventListener('scroll', listener)
  }

  /**
   * Navbar links active state on scroll
   * Activates whichever link's section top was most recently scrolled past,
   * so there is always exactly one active link with no dead zone between sections.
   */
  let navbarlinks = select('#navbar .scrollto', true)
  const navbarlinksActive = () => {
    let position = window.scrollY + 200
    let current = null
    navbarlinks.forEach(navbarlink => {
      if (!navbarlink.hash) return
      let section = select(navbarlink.hash)
      if (!section) return
      if (section.offsetTop <= position) {
        if (!current || section.offsetTop > current.offsetTop) {
          current = section
          current.navbarlink = navbarlink
        }
      }
    })
    navbarlinks.forEach(navbarlink => {
      if (!navbarlink.hash) return
      navbarlink.classList.toggle('active', current && current.navbarlink === navbarlink)
    })
  }
  window.addEventListener('load', navbarlinksActive)
  onscroll(document, navbarlinksActive)

  /**
   * Scrolls to an element with header offset
   */
  const scrollto = (el) => {
    let header = select('#header')
    let offset = header.offsetHeight

    if (!header.classList.contains('header-scrolled')) {
      offset -= 20
    }

    let elementPos = select(el).offsetTop
    window.scrollTo({
      top: elementPos - offset,
      behavior: 'smooth'
    })
  }

  /**
   * Toggle .header-scrolled class to #header when page is scrolled
   */
  let selectHeader = select('#header')
  if (selectHeader) {
    const headerScrolled = () => {
      if (window.scrollY > 100) {
        selectHeader.classList.add('header-scrolled')
      } else {
        selectHeader.classList.remove('header-scrolled')
      }
    }
    window.addEventListener('load', headerScrolled)
    onscroll(document, headerScrolled)
  }

  /**
   * Back to top button
   */
  let backtotop = select('.back-to-top')
  if (backtotop) {
    const toggleBacktotop = () => {
      if (window.scrollY > 100) {
        backtotop.classList.add('active')
      } else {
        backtotop.classList.remove('active')
      }
    }
    window.addEventListener('load', toggleBacktotop)
    onscroll(document, toggleBacktotop)
  }

  /**
   * Mobile nav toggle
   */
  function setMobileNavIcon(toggleEl, isOpen) {
    if (!toggleEl) return
    let icon = toggleEl.querySelector('i') || toggleEl
    icon.classList.toggle('bi-list', !isOpen)
    icon.classList.toggle('bi-x', isOpen)
    toggleEl.setAttribute('aria-expanded', isOpen ? 'true' : 'false')
  }

  function closeMobileNav() {
    let navbar = select('#navbar')
    if (navbar && navbar.classList.contains('navbar-mobile')) {
      navbar.classList.remove('navbar-mobile')
      setMobileNavIcon(select('.mobile-nav-toggle'), false)
    }
  }

  on('click', '.mobile-nav-toggle', function(e) {
    let navbar = select('#navbar')
    let isOpen = navbar.classList.toggle('navbar-mobile')
    setMobileNavIcon(this, isOpen)
  })

  /**
   * Close mobile nav on Escape
   */
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeMobileNav()
    }
  })

  /**
   * Mobile nav dropdowns activate
   */
  on('click', '.navbar .dropdown > a', function(e) {
    // href="#" toggle — never let it jump the page
    e.preventDefault()
    let expanded = this.nextElementSibling.classList.toggle('dropdown-active')
    this.setAttribute('aria-expanded', expanded ? 'true' : 'false')
  }, true)

  /**
   * Scrool with ofset on links with a class name .scrollto
   */
  on('click', '.scrollto', function(e) {
    if (select(this.hash)) {
      e.preventDefault()

      closeMobileNav()
      scrollto(this.hash)
    }
  }, true)

  /**
   * Scroll with ofset on page load with hash links in the url
   */
  window.addEventListener('load', () => {
    if (window.location.hash) {
      if (select(window.location.hash)) {
        scrollto(window.location.hash)
      }
    }
  });

  /**
   * Preloader
   */
  let preloader = select('#preloader');
  if (preloader) {
    window.addEventListener('load', () => {
      preloader.remove()
    });
  }

  /**
   * Animation on scroll
   */
  window.addEventListener('load', () => {
    AOS.init({
      duration: 1000,
      easing: "ease-in-out",
      once: true,
      mirror: false
    });
  });

  /**
   * Homepage contact form — opens the visitor's mail client with a prefilled message
   */
  (function () {
    let form = document.getElementById('gst-contact-form');
    if (!form) return;
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      let name = form.name.value.trim();
      let email = form.email.value.trim();
      let subject = form.subject.value.trim();
      let message = form.message.value.trim();

      let body = 'Name: ' + name + '\nEmail: ' + email + '\n\n' + message;
      let mailto = 'mailto:support@gscalabletech.com'
        + '?subject=' + encodeURIComponent(subject)
        + '&body=' + encodeURIComponent(body);

      window.location.href = mailto;
    });
  })();

  /**
   * Resume upload pre-check on the application form. Guarded — the input only
   * exists on that one page.
   */
  (function () {
    const resumeInput = select('#resume_path');
    if (!resumeInput) return;

    const maxSize = 5 * 1024 * 1024; // 5MB
    const allowed = ['pdf', 'doc', 'docx'];

    // Reuse the field's own error node so the message reads like server-side
    // validation instead of interrupting with an alert().
    let notice = document.getElementById('resume_path_error');
    if (!notice) {
      notice = document.createElement('p');
      notice.id = 'resume_path_error';
      notice.className = 'field-error';
      resumeInput.parentNode.appendChild(notice);
    }

    resumeInput.addEventListener('change', function () {
      notice.textContent = '';
      resumeInput.classList.remove('is-invalid');

      if (!resumeInput.files.length) return;

      const file = resumeInput.files[0];
      const extension = file.name.split('.').pop().toLowerCase();

      if (allowed.indexOf(extension) === -1) {
        notice.textContent = 'Please upload a PDF, DOC or DOCX file.';
        resumeInput.classList.add('is-invalid');
        resumeInput.value = '';
        return;
      }

      if (file.size > maxSize) {
        notice.textContent = 'That file is larger than the 5MB limit. Please upload a smaller file.';
        resumeInput.classList.add('is-invalid');
        resumeInput.value = '';
      }
    });
  })();

})()

