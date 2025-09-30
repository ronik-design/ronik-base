import React from "react";

function TopNav({ mode = 'light' }) {
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

  return (
    <>
      <div className={`top-nav ${mode === 'dark' ? 'top-nav--dark' : ''}`}>
        <div className="top-nav-left">
          {mode === 'dark' ? (
            <img src="/wp-content/plugins/ronik-base/assets/images/logo-dark.svg" alt="Ronik Base Logo" />
          ) : (
            <img src="/wp-content/plugins/ronik-base/assets/images/logo.svg" alt="Ronik Base Logo" />
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
