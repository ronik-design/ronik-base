import React from "react";

function TopNav({ mode = 'light' }) {
  // Get wpVars from WordPress localization
  const wpVars = typeof window !== 'undefined' && window.wpVars ? window.wpVars : {};
  const betaMode = wpVars.betaMode || false;
  const pluginSlug = wpVars.pluginSlug || 'ronik-base';

  // Function to check if current page is active based on query parameter
  const isActive = (href) => {
    if (typeof window !== 'undefined') {
      const urlParams = new URLSearchParams(window.location.search);
      const currentPage = urlParams.get('page');
      const hrefPage = href.split('page=')[1];
      return currentPage === hrefPage;
    }
    return false;
  };

  let navItems = [
    {
      label: "Dashboard",
      href: "/wp-admin/admin.php?page=options-ronik-base_media_cleaner",
    },
    {
      label: "Preserved Media",
      href: "/wp-admin/admin.php?page=options-ronik-base_preserved",
    },
    {
      label: "About",
      href: "/wp-admin/admin.php?page=options-ronik-base_media_cleaner_about",
    },
    {
      label: "Settings",
      href: "/wp-admin/admin.php?page=options-ronik-base_settings_media_cleaner",
    },
    {
      label: "Support",
      href: "/wp-admin/admin.php?page=options-ronik-base_support_media_cleaner",
    },
  ];

  // Remove Settings if betaMode is on
  if (betaMode) {
    navItems = navItems.filter(item => item.label !== "Settings");
  }

  return (
    <>
      <div className={`top-nav ${mode === 'dark' ? 'top-nav--dark' : ''}`}>
        <div className="top-nav-left">
          {mode === 'dark' ? (
            <img src={`/wp-content/plugins/${pluginSlug}/assets/images/logo-dark.svg`} alt="Ronik Base Logo" />
          ) : (
            <img src={`/wp-content/plugins/${pluginSlug}/assets/images/logo.svg`} alt="Ronik Base Logo" />
          )}
        </div>
        <div className="top-nav-right">
          {navItems.map((item, index) => (
            <a 
              href={item.href} 
              key={index} 
              className={`top-nav-right-item ${isActive(item.href) ? 'active' : ''}`}
            >
              {item.label}
            </a>
          ))}
        </div>
      </div>
    </>
  );
}

export default TopNav;
