[![Build Status](https://travis-ci.com/DiscipleTools/disciple-tools-theme.svg?branch=master)](https://travis-ci.com/DiscipleTools/disciple-tools-theme)

# Disciple Tools
Disciple.Tools software boosts collaboration, clarity, and accountability for disciple and church multiplication movements.

## Description
As a contact relationship management (CRM) system it is :

- unique – able to track and organize individuals or groups generationally
- insightful - giving end-to-end dashboards, charts, and maps on contacts, baptisms, groups, churches, and movements
- secure – restricting database access based on permission levels and specific assignments
- federated - designed to host how and where you want and inter-link instances as desired
- scalable – relevant for individuals, groups, or movements
- customizable – highly adaptable through settings, built-in modifications, external plugins, and  requiring low-tech skills
- multilingual  – translatable, facilitating cross-cultural collaboration
- mobile-friendly - giving full-functionality from a mobile device
- free and open source – created in the WordPress environment and improved by a volunteer community on Github (https://github.com/DiscipleTools/disciple-tools)

The commit team will shape the development of Disciple.Tools, and is currently implementing generational mapping, a mobile app with offline use, and people group tracking.

## Purpose
There needs to exist a simple, low-cost, highly distributable CRM that is tailored to the process of using digital marketing to accelerate disciple making movements. Most CRM solutions are too expensive to be used by small volunteer teams, and they often require significant configuration and development to implement. This project is attempting to make a rapid launch, low cost system that is tailored for movements.

## Platform
We are building on the Wordpress platform because of its open-source availability, simplicity of installation, numerous low cost hosting options, multi-lingual support, substantial configurability and customization, giant development community and resources, native REST API, mobile readiness, and healthy market place for distributing themes and plugins.

## Guidebook

Visit our [online guidebook](https://disciple.tools/user-docs)


## Theme

This repository contains only the WordPress theme. There are a growing number of plugin extensions to the theme, but the core of the Disciple Tools system is this theme.

## Support

If you need support, email us at supportXXXdisciple.tools, replacing XXXX with the @ symbol. You can also read articles in our knowledge base at http://help.disciple.tools.

---

## How to Install
The goal of the project is to create a disciple-making CRM that is incredibly simple, cheap, and fast to launch. Below are the simple steps to do that.

**Note: You must have PHP 7.0 or above. PHP 5.6 will not work.**

### Step 1
- Download the theme disciple-tools-theme.zip file from the Disciple-Tools-Theme GitHub release page (https://github.com/DiscipleTools/disciple-tools-theme/releases)

### Step 2
- Open up your Wordpress site.
- Login to your Admin Dashboard. `http://{your website}/wp-admin/`

> Note: You have to be an administrator with the permissions to install plugins.

### Step 3
- In the Admin area, go to `Appearance > Themes` in the left navigation. This is where themes are installed.
- Select the `Add New` button at the top of the screen.
- Then select the `"Upload Theme` button at the top of the screen.
- Use the `choose file` button to find the disciple-tools-theme.zip file you saved in step 1, and upload that file and wait for Wordpress to install it.

### Step 4
- Once uploaded, you will see the new Disciple Tools Theme installed with other themes. Next `Activate` the theme.

Done! You now have a complete coalition management system for your movement.
You can access it by clicking on the home button at the top of the navigation bar.

 Blessings!
 



## How to Contribute

Follow these steps.

1. Fork it!
1. Create your feature branch: `git checkout -b my-new-feature`
1. Commit your changes: `git commit -am 'Add some feature'`
1. Push to the branch: `git push origin my-new-feature`
1. Submit a pull request

To apply your changes to the plugin, update the contents of the `wp-content/themes/disciple-tools-theme` folder in your WordPress installation.

Make sure tests are passing!

[Read more in `CONTRIBUTING`](https://github.com/DiscipleTools/disciple-tools-theme/wiki/Contribution-guidelines)

 ## Setup for Developers

Composer

  Install via Homebrew or run Installer:
```
$ brew update
$ brew install composer
```
  Run Composer to install dependencies
```
$ composer install
```
PHP Code Sniffer

  Run `./vendor/bin/phpcs` to see list of PHP format errors

  Run ```./vendor/bin/phpcbf``` to auto-fix all possible format errors


## How to responsibly disclose a security vulnerability

If you discover a security vulnerability in these WordPress plugins and/or themes, or in the website https://disciple.tools , please send an email to supportXXXXdisciple.tools , replacing XXXX with the @ symbol. We ask that you give us a reasonable amount of time to correct the issue before you make the vulnerability public. Please do not submit a GitHub issue or a GitHub pull request, as these are public.

## Community Projects
1. [Installing Disciple Tools with Kubernetes on Google](https://github.com/cairocoder01/disciple-tools-kubernetes) (Github Project)
2. [Docker Image of DT](https://github.com/cairocoder01/dt-docker) (Github Project)
1. [Disciple Tools Mobile API](https://github.com/cairocoder01/dt-mobile-api) (Github Project)
