// Share Job Script
function shareJob() {
  const jobUrl = window.location.href;
  const jobTitle = "{{ $job->title }} at Global Scalable Technologies (GST)";
  
  if (navigator.share) {
    navigator.share({
      title: jobTitle,
      text: 'Check out this job opportunity: ' + jobTitle,
      url: jobUrl,
    })
    .catch(error => console.log('Error sharing:', error));
  } else {
    // Fallback for browsers that don't support share API
    const tempInput = document.createElement('input');
    document.body.appendChild(tempInput);
    tempInput.value = jobUrl;
    tempInput.select();
    document.execCommand('copy');
    document.body.removeChild(tempInput);
    
    alert('Job URL copied to clipboard! Share it with your friends.');
  }
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
   * Initiate glightbox 
   */
  const glightbox = GLightbox({
    selector: '.glightbox'
  });

  function getCurrentScroll() {
    return window.pageYOffset || document.documentElement.scrollTop;
  }

  /**
   * Porfolio isotope and filter
   */
  window.addEventListener('load', () => {

    let portfolioContainer = select('.portfolio-container');

    if (portfolioContainer) {
      let portfolioIsotope = new Isotope(portfolioContainer, {
        itemSelector: '.portfolio-item'
      });

      let portfolioFilters = select('#portfolio-flters li', true);

      on('click', '#portfolio-flters li', function(e) {
        e.preventDefault();
        portfolioFilters.forEach(function(el) {
          el.classList.remove('filter-active');
        });
        this.classList.add('filter-active');

        portfolioIsotope.arrange({
          filter: this.getAttribute('data-filter')
        });
        portfolioIsotope.on('arrangeComplete', function() {
          AOS.refresh()
        });

      }, true);
    }

  });

  /**
   * Initiate portfolio lightbox 
   */
  const portfolioLightbox = GLightbox({
    selector: '.portfolio-lightbox'
  });

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

})()





document.getElementById('resume_path').addEventListener('change', function() {
  const fileInput = this;
  const maxSize = 5 * 1024 * 1024; // 5MB in bytes
  
  if (fileInput.files.length > 0) {
      const fileSize = fileInput.files[0].size;
      
      if (fileSize > maxSize) {
          alert('The selected file is too large. Maximum allowed size is 5MB.');
          fileInput.value = ''; // Clear the file input
      }
  }
});