# dt-mapping-module
This module is intended to be a reusable module for Disciple Tools plugins and themes. It provides the core
amchart and amchart maps modules, the core location_grid database, keyed GeoJSON assets, and some admin utilities
for themes and plugins to hook into.

1. It contains an independent migration engine as a way to manage upgrades to the custom data tables. 

## Data Source
The primary data source for Disciple Tools mapping data comes from the Geonames Project. [www.location_grid.org](https://www.location_grid.org/)


## Database Tables
The two database tables installed are deliberately not prefixed, so that in a multisite environment these
tables are only installed once with all the data. This also allows upgrades to be once per database. All other 
custom location data uses these two tables as reference, but stores these in site specific locations.
1. dt_location_grid
1. dt_location_grid_hierarchy

## Adding New Polygons


## Hooking and Filtering

## Migrations
| Number        | Description          
| ------------- |-----------------------------------------------------------------------------------------| 
| 0000-initial.php | This installs the initial database tables. dt_location_grid, dt_location_grid_hierarchy | 


## Other Modules

phpGeo
phpgeocoder
