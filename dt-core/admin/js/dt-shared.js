/*
This javascript file is enqueued on all admin pages.
shared scripts applicable to all sections.
 */
"use strict";

window.dt_admin_shared = {
  escape(str) {
    if (typeof str !== "string") return str;
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&apos;");
  }
}