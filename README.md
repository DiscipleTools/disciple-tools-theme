[![Build Status](https://travis-ci.org/DiscipleTools/disciple-tools-theme.svg?branch=master)](https://travis-ci.org/DiscipleTools/disciple-tools-theme)

# Disciple Tools
A disciple relationship management system for discipleship making movements.

## Description
Disciple Tools is a disciple relationship management (DRM) system especially designed to support outreach projects using digital marketing to accelerate disciple making movements (DMM).

## Purpose
There needs to exist a simple, low-cost, highly distributable DRM that is tailored to the process of using digital marketing to accelerate disciple making movements. Most DRM solutions are too expensive to be used by small volunteer teams, and they often require significant configuration and development to implement. This project is attempting to make a rapid launch, low cost system that is tailored for movements.

## Platform
We are building on the Wordpress platform because of its open-source availability, simplicity of installation, numerous low cost hosting options, multi-lingual support, substantial configurability and customization, giant development community and resources, (soon to be) native REST API, mobile readiness, and healthy market place for distributing themes and plugins.

## Wiki

Don't forget, we have a [wiki for Disciple Tools](https://github.com/DiscipleTools/disciple-tools/wiki).

## Theme

This repository contains only the WordPress plugin for a Disciple Tools CRM. Another repository contains the theme also needed to run the CRM:
https://github.com/DiscipleTools/disciple-tools-theme

## Support

If you need support, email us at supportXXXdisciple.tools, replacing XXX with the @ symbol. You can also read articles in our knowledge base at http://help.disciple.tools.

---

## How to Install
The goal of the project is to create a disciple-making DRM that is incredibly simple, cheap, and fast to launch. Below are the simple steps to do that.

### Step 1
- Download the plugin .zip file from the Disciple-Tools GitHub release page (https://github.com/DiscipleTools/disciple-tools/releases). Save it to your desktop of anywhere so that you can easily find it in a minute.
- Download the theme .zip file from the Disciple-Tools-Theme GitHub release page (https://github.com/DiscipleTools/disciple-tools-theme/releases)

### Step 2
- Open up your Wordpress site, login to your Admin Dashboard.
- Then on the admin navigation, go to "Plugins".

> Note: You have to be an administrator with the permissions to install plugins.

### Step 3
- At the top of the "Add New" plugins screen, select the "Upload Plugin" button at the top of the page.
- Use the file upload tool to upload the plugin .zip file you saved in Step #1.

### Step 4
- Once uploaded, "Activate" the plugin.
> Note: You can find the "Activate" link for the plugin both on the screen on which you installed it, and on the "Installed Plugins" screen under the "Plugins" admin menu.

### Step 5
- In the navigation bar, go to "Appearance" then "Themes"

### Step 6
- At the top of the "Add New" themes screen, select the "Upload Theme" button at the top of the page.
- Use the file upload tool to upload the theme .zip file you saved in Step #1.

### Step 7
- When you activate the theme, WordPress will prompt you to install 3 plugins:
- "Psalm 119" and "JWT Authentication for WP-API" are required, install them.
- "Disciple Tools - Demo" allows you to add/remove sample data to your database so that you can explore the disciple-tools CRM more effectively. Installing it is recommended but not required.

Done! You now have a complete disciple-making CRM for your movement.
You can access it by clicking on the home button at the top of the navigation bar.


 Blessings!

## How to Contribute

Follow these steps.

1. Fork it!
1. Create your feature branch: `git checkout -b my-new-feature`
1. Commit your changes: `git commit -am 'Add some feature'`
1. Push to the branch: `git push origin my-new-feature`
1. Submit a pull request

> Note: you may also want to fork the theme repo (https://github.com/DiscipleTools/disciple-tools-theme)

To apply your changes to the plugin, update the contents of the `wp-content\plugins\disciple-tools` folder in your WordPress installation.

Make sure tests are passing!

[Read more in `CONTRIBUTING.md`](./CONTRIBUTING.md)


## How to responsibly disclose a security vulnerability

If you discover a security vulnerability in these WordPress plugins and/or themes, or in the website https://disciple.tools , please send an email to supportXXXdisciple.tools , replacing XXXX with the @ symbol. We ask that you give us a reasonable amount of time to correct the issue before you make the vulnerability public. Please do not submit a GitHub issue or a GitHub pull request, as these are public.
