# Location Field Import Guide

This guide explains how to import location data using the DT CSV Import feature.

## Location Field Types

### 1. `location_grid`
- **Purpose**: Direct reference to location grid entries
- **Required Format**: Numeric grid ID only
- **Example**: `12345`
- **Validation**: Must be a valid grid ID that exists in the location grid table

### 2. `location_grid_meta`
- **Purpose**: Flexible location data with optional geocoding
- **Supported Formats**:
  - **Numeric grid ID**: `12345`
  - **Decimal coordinates**: `40.7128, -74.0060` (latitude, longitude)
  - **DMS coordinates**: `35°50′40.9″N, 103°27′7.5″E` (degrees, minutes, seconds)
  - **Address**: `123 Main St, New York, NY 10001`
  - **Multiple locations**: `Paris, France; Berlin, Germany` (separated by semicolons)
- **Geocoding**: Can use Google Maps or Mapbox to convert addresses to coordinates
- **Multi-location Support**: Multiple addresses, coordinates, or grid IDs can be separated by semicolons

### 3. `location` (Legacy)
- **Purpose**: Generic location field
- **Behavior**: 
  - Numeric values treated as grid IDs
  - Other values treated as location_grid_meta

## Geocoding Services

### Available Services
1. **Google Maps** - Requires Google Maps API key
2. **Mapbox** - Requires Mapbox API key
3. **None** - No geocoding (addresses saved as-is)

### Geocoding Process
When a geocoding service is selected:

1. **Addresses** → Converted to coordinates → Assigned to location grid
2. **Coordinates** → Assigned to location grid → Address lookup (reverse geocoding)
3. **Grid IDs** → Validated and used directly

### Rate Limiting
- Automatic delays added for large imports to respect API limits
- Batch processing available for performance

## CSV Format Examples

### location_grid Field
```csv
name,location_grid
John Doe,12345
Jane Smith,67890
```

### location_grid_meta Field
```csv
name,location_grid_meta
John Doe,12345
Jane Smith,"40.7128, -74.0060"
Bob Johnson,"123 Main St, New York, NY"
Alice Brown,"Paris, France; Berlin, Germany"
Charlie Davis,"40.7128,-74.0060; 34.0522,-118.2437"
Eve Wilson,"35°50′40.9″N, 103°27′7.5″E"
Frank Miller,"40°42′46″N, 74°0′21″W; 51°30′26″N, 0°7′39″W"
```

### Mixed Location Data
```csv
name,location_grid_meta,notes
Person 1,12345,Direct grid ID
Person 2,"40.7128, -74.0060",Decimal coordinates
Person 3,"35°50′40.9″N, 103°27′7.5″E",DMS coordinates
Person 4,"New York City",Address (requires geocoding)
Person 5,"Tokyo, Japan; London, UK",Multiple addresses
Person 6,"12345; 67890",Multiple grid IDs
Person 7,"40.7128,-74.0060; Big Ben, London",Mixed decimal coordinates and address
Person 8,"35°41′22″N, 139°41′30″E; Paris, France",Mixed DMS coordinates and address
```

## Coordinate Formats

### DMS (Degrees, Minutes, Seconds) Format
The system supports DMS coordinates in various formats:

**Standard Format:**
- `35°50′40.9″N, 103°27′7.5″E` (with proper symbols)
- `40°42′46″N, 74°0′21″W` (integer seconds)

**Alternative Symbols:**
- `35d50m40.9sN, 103d27m7.5sE` (using d/m/s)
- `35°50'40.9"N, 103°27'7.5"E` (using regular quotes)

**Requirements:**
- Direction indicators (N/S/E/W) are **required**
- Degrees: 0-180 for longitude, 0-90 for latitude
- Minutes: 0-59
- Seconds: 0-59.999 (decimal seconds supported)
- Comma separation between latitude and longitude

**Examples:**
- Beijing: `39°54′26″N, 116°23′29″E`
- London: `51°30′26″N, 0°7′39″W`
- Sydney: `33°51′54″S, 151°12′34″E`

### Decimal Degrees Format
- Standard format: `40.7128, -74.0060`
- Negative values for South (latitude) and West (longitude)
- Range: -90 to 90 for latitude, -180 to 180 for longitude

## Configuration

### Setting Up Geocoding
1. Configure API keys in DT settings:
   - Google Maps: Settings → Mapping → Google Maps API
   - Mapbox: Settings → Mapping → Mapbox API

2. Select geocoding service during import process

### Import Process
1. Upload CSV file
2. Map columns to fields
3. For location_grid_meta fields, select geocoding service
4. Preview import to verify field mapping (addresses shown as-is, no geocoding performed)
5. Execute import (geocoding happens during actual import)

## Error Handling

### Common Issues
- **Invalid grid ID**: Non-existent grid IDs will cause import errors
- **Invalid coordinates**: Out-of-range lat/lng values will be rejected
- **Geocoding failures**: Addresses that can't be geocoded will be saved as-is with error notes
- **API limits**: Rate limiting may slow down large imports

### Error Resolution
- Check API key configuration
- Verify data formats
- Review geocoding service status
- Use smaller batch sizes for large imports

## Best Practices

1. **Validate Data**: Check grid IDs and coordinate formats before import
2. **Use Consistent Formats**: Stick to one format per field when possible
3. **Test Small Batches**: Test with a few records before large imports
4. **Monitor API Usage**: Be aware of geocoding API limits and costs
5. **Backup Data**: Always backup before large imports
6. **Multiple Locations**: Use semicolons to separate multiple addresses, coordinates, or grid IDs
7. **Rate Limiting**: For multiple locations with geocoding, automatic delays prevent API rate limiting
8. **Preview Mode**: Preview shows raw addresses as entered in CSV - geocoding only happens during actual import

## Technical Details

### Field Handlers
- `handle_location_grid_field()`: Validates numeric grid IDs
- `handle_location_grid_meta_field()`: Processes flexible location data
- `DT_CSV_Import_Geocoding`: Handles all geocoding operations

### Data Flow
1. Raw CSV value input
2. Field type detection
3. Format validation
4. Geocoding (if enabled)
5. Location grid assignment
6. Data formatting for DT_Posts API
7. Record creation

### Performance Considerations
- Geocoding adds processing time
- Large imports may take longer with geocoding enabled
- Background processing available for large datasets
- Progress tracking during import

## Support

For issues with location imports:
1. Check error logs for detailed error messages
2. Verify API key configuration
3. Test with sample data
4. Review CSV format requirements
5. Contact system administrator if needed 