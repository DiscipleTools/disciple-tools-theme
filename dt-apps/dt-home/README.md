# DT Home Screen Magic Link App

## Overview

This is a self-contained magic link app for the Disciple.Tools theme that provides a Home Screen dashboard functionality. It replaces the functionality previously provided by the `@dt-home/` plugin, but is now integrated directly into the theme.

## Structure

```
dt-apps/dt-home/
├── magic-link-home-app.php          # Main magic link app class
├── dt-home-loader.php               # Main loader file
├── admin/
│   └── home-admin.php               # Admin interface (hooks into theme settings)
├── frontend/
│   └── home-screen.php              # Main dashboard template
├── assets/
│   ├── css/
│   │   ├── home-screen.css          # Frontend styles
│   │   └── admin.css                 # Admin styles
│   └── js/
│       ├── home-screen.js           # Frontend JavaScript
│       └── admin.js                  # Admin JavaScript
└── includes/                         # Additional classes (future phases)
```

## Features (Phase 1)

### ✅ Completed
- **Magic Link App Structure** - Follows the existing theme pattern
- **Home Screen Dashboard** - Basic frontend interface
- **Admin Integration** - Hooks into theme settings as a new tab
- **Responsive Design** - Mobile-friendly layout
- **Theme Integration** - Loaded via theme's functions.php

### 🔄 Future Phases
- **Apps Management** - CRUD for custom apps
- **Training Videos** - Video management and display
- **User Authentication** - Enhanced login/logout
- **Settings Management** - Advanced configuration options

## Usage

### For Users
1. Navigate to your user settings in Disciple.Tools
2. Find the "Home Screen" app in your apps list
3. Click to generate your magic link
4. Access your personalized dashboard

### For Administrators
1. Go to Settings (D.T) in the WordPress admin
2. Click on the "Home Screen" tab (or use the direct submenu item)
3. Configure general settings, apps, and training videos

## How to Test Phase 1

### Admin Interface Testing
1. **Direct Access**: Go to Settings (D.T) → Home Screen (submenu item)
2. **Tab Navigation**: Go to Settings (D.T) → click "Home Screen" tab
3. **Settings Form**: Test saving general settings (title, description, training videos toggle)
4. **Form Validation**: Try submitting empty title to test validation

### Magic Link Testing
1. **User Settings**: Go to user profile → Your Apps section
2. **App Generation**: Look for "Home Screen" app in the list
3. **Magic Link**: Click to generate/copy the magic link
4. **Frontend Access**: Visit the generated magic link URL
5. **Dashboard Display**: Verify the home screen loads with placeholder content

### Frontend Testing
1. **Responsive Design**: Test on mobile/tablet/desktop
2. **Loading States**: Check loading spinners work
3. **Quick Actions**: Test Settings and Logout buttons
4. **Error Handling**: Verify error messages display properly

## Development

This app follows the established pattern for magic link apps in the Disciple.Tools theme:

1. **Magic Link Class** - Extends `DT_Magic_Url_Base`
2. **REST Endpoints** - Uses WordPress REST API
3. **Admin Integration** - Hooks into theme settings system
4. **Frontend Templates** - PHP templates with WordPress standards
5. **Asset Management** - Uses WordPress enqueue system

## Integration Points

- **Theme Settings**: `dt_settings_tab_menu` and `dt_settings_tab_content` hooks
- **Magic Link System**: Standard magic link registration
- **User Management**: Integrates with existing user system
- **Asset Loading**: Uses theme's asset management system

## Future Development

This structure provides a clean pattern for future magic link apps:

1. Create new directory under `dt-apps/`
2. Follow the same structure
3. Add loader to `functions.php`
4. Hook into theme systems as needed

## Phase 2 Status: ✅ COMPLETE

### ✅ Phase 2 Completed Features
- **Apps Management** - Full CRUD operations for custom apps
- **Training Videos** - Full CRUD operations for training videos  
- **Admin Interface** - Complete management interface
- **Frontend Display** - Real apps and videos display
- **Data Persistence** - WordPress options API storage
- **Validation** - Input validation and error handling

## How to Test Phase 2

### Admin Interface Testing
1. **Apps Management**:
   - Go to Settings (D.T) → Home Screen
   - Test creating new apps with different settings
   - Test editing existing apps
   - Test deleting apps
   - Verify apps appear in the list with correct information

2. **Training Videos Management**:
   - Test creating new training videos with YouTube URLs
   - Test editing video details (title, description, duration, category)
   - Test deleting videos
   - Verify videos appear in the list with thumbnails

3. **Form Validation**:
   - Try submitting empty required fields
   - Test invalid URLs
   - Verify error messages display properly

### Frontend Testing
1. **Real Data Display**:
   - Generate a magic link from user settings
   - Visit the magic link URL
   - Verify real apps and videos load (not placeholders)
   - Test clicking on apps and videos (should open in new tab)

2. **Dynamic Content**:
   - Add new apps/videos in admin
   - Refresh the frontend to see changes
   - Test with different app colors and icons

3. **Error Handling**:
   - Test with no apps/videos configured
   - Verify appropriate "no content" messages

### Data Persistence Testing
1. **Settings Persistence**:
   - Change general settings and save
   - Refresh page to verify settings persist
   - Test enabling/disabling training videos

2. **Content Persistence**:
   - Add apps and videos
   - Refresh admin page to verify they persist
   - Test editing and verify changes save

## Phase 1 Status: ✅ COMPLETE
## Phase 2 Status: ✅ COMPLETE

The core functionality is now complete. Ready for Phase 3 development (enhanced features).
