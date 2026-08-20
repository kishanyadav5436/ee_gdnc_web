/**
 * @file
 * EE GNDEC Navigation JavaScript
 * Handles sticky header, mobile menu toggle, and dropdown interactions.
 */

(function (Drupal) {
  'use strict';

  Drupal.behaviors.eeGndecNavigation = {
    attach: function (context, settings) {

      // ── Sticky header on scroll ──────────────────────────────
      const header = document.querySelector('.site-header');
      if (header) {
        const onScroll = () => {
          header.classList.toggle('scrolled', window.scrollY > 20);
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll(); // run once on attach
      }

      // ── Mobile hamburger toggle ──────────────────────────────
      const toggle = context.querySelector ? context.querySelector('.nav-toggle') : null;
      const navEl  = document.querySelector('.primary-nav');

      if (toggle && navEl) {
        toggle.addEventListener('click', function () {
          const isOpen = navEl.classList.toggle('open');
          toggle.classList.toggle('open', isOpen);
          toggle.setAttribute('aria-expanded', isOpen);
          // Prevent body scroll when nav is open
          document.body.style.overflow = isOpen ? 'hidden' : '';
        });
      }

      // ── Mobile sub-menu toggle (tap on parent link) ──────────
      const parentLinks = document.querySelectorAll('.primary-nav__link[data-has-dropdown]');
      parentLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
          if (window.innerWidth <= 1024) {
            e.preventDefault();
            const parentItem = link.closest('.primary-nav__item');
            if (parentItem) {
              parentItem.classList.toggle('open-sub');
            }
          }
        });
      });

      // ── Close mobile nav when a leaf link is clicked ─────────
      const leafLinks = document.querySelectorAll('.primary-nav__dropdown a, .primary-nav__link:not([data-has-dropdown])');
      leafLinks.forEach(function (link) {
        link.addEventListener('click', function () {
          if (navEl) { navEl.classList.remove('open'); }
          if (toggle) {
            toggle.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
          }
          document.body.style.overflow = '';
        });
      });

      // ── Close nav on Escape key ──────────────────────────────
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && navEl && navEl.classList.contains('open')) {
          navEl.classList.remove('open');
          if (toggle) {
            toggle.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
          }
          document.body.style.overflow = '';
          toggle && toggle.focus();
        }
      });

      // ── Mark active link based on current path ───────────────
      const currentPath = window.location.pathname;
      document.querySelectorAll('.primary-nav__link').forEach(function (link) {
        const href = link.getAttribute('href');
        if (href && (href === currentPath || (href !== '/' && currentPath.startsWith(href)))) {
          link.classList.add('active');
        }
      });

      // ── Notice ticker animation pause on hover ───────────────
      const ticker = document.querySelector('.notice-ticker__text');
      if (ticker) {
        ticker.addEventListener('mouseenter', function () {
          ticker.style.animationPlayState = 'paused';
        });
        ticker.addEventListener('mouseleave', function () {
          ticker.style.animationPlayState = 'running';
        });
      }

    }
  };

}(Drupal));
