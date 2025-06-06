<?php
/**
 * CSV Import Documentation Modal Template
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div id="dt-import-documentation-modal" class="dt-import-modal" style="display: none;">
    <div class="dt-import-modal-content">
        <div class="dt-import-modal-header">
            <h2><?php esc_html_e( 'CSV Import Documentation', 'disciple_tools' ); ?></h2>
            <button type="button" class="dt-import-modal-close" id="dt-import-docs-close">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        
        <div class="dt-import-modal-body">
            <div class="dt-import-docs-nav">
                <ul class="dt-import-docs-tabs">
                    <li><a href="#overview" class="active"><?php esc_html_e( 'Overview', 'disciple_tools' ); ?></a></li>
                    <li><a href="#field-types"><?php esc_html_e( 'Field Types', 'disciple_tools' ); ?></a></li>
                    <li><a href="#examples"><?php esc_html_e( 'Examples', 'disciple_tools' ); ?></a></li>
                    <li><a href="#troubleshooting"><?php esc_html_e( 'Troubleshooting', 'disciple_tools' ); ?></a></li>
                </ul>
            </div>
            
            <div class="dt-import-docs-content">
                <!-- Overview Tab -->
                <div id="overview" class="dt-import-docs-tab-content active">
                    <h3><?php esc_html_e( 'Getting Started', 'disciple_tools' ); ?></h3>
                    <p><?php esc_html_e( 'The CSV Import tool allows you to bulk import contacts and groups into Disciple Tools. Follow these steps:', 'disciple_tools' ); ?></p>
                    
                    <ol>
                        <li><strong><?php esc_html_e( 'Select Record Type', 'disciple_tools' ); ?>:</strong> <?php esc_html_e( 'Choose Contacts or Groups', 'disciple_tools' ); ?></li>
                        <li><strong><?php esc_html_e( 'Upload CSV', 'disciple_tools' ); ?>:</strong> <?php esc_html_e( 'Upload your prepared CSV file (max 10MB)', 'disciple_tools' ); ?></li>
                        <li><strong><?php esc_html_e( 'Map Fields', 'disciple_tools' ); ?>:</strong> <?php esc_html_e( 'Map your CSV columns to Disciple Tools fields', 'disciple_tools' ); ?></li>
                        <li><strong><?php esc_html_e( 'Preview & Import', 'disciple_tools' ); ?>:</strong> <?php esc_html_e( 'Review and execute the import', 'disciple_tools' ); ?></li>
                    </ol>
                    
                    <h4><?php esc_html_e( 'File Requirements', 'disciple_tools' ); ?></h4>
                    <ul>
                        <li><?php esc_html_e( 'File Type: CSV (.csv) files only', 'disciple_tools' ); ?></li>
                        <li><?php esc_html_e( 'Maximum Size: 10MB', 'disciple_tools' ); ?></li>
                        <li><?php esc_html_e( 'Encoding: UTF-8 recommended', 'disciple_tools' ); ?></li>
                        <li><?php esc_html_e( 'Headers Required: First row must contain column headers', 'disciple_tools' ); ?></li>
                        <li><?php esc_html_e( 'Consistent Columns: All rows must have the same number of columns', 'disciple_tools' ); ?></li>
                    </ul>
                    
                    <div class="dt-import-tip">
                        <strong><?php esc_html_e( 'Best Practice:', 'disciple_tools' ); ?></strong>
                        <?php esc_html_e( 'Start with a small test file (5-10 records) to verify your formatting before importing large datasets.', 'disciple_tools' ); ?>
                    </div>
                </div>
                
                <!-- Field Types Tab -->
                <div id="field-types" class="dt-import-docs-tab-content">
                    <h3><?php esc_html_e( 'Supported Field Types', 'disciple_tools' ); ?></h3>
                    
                    <div class="dt-import-field-type">
                        <h4><?php esc_html_e( 'Text & Text Area', 'disciple_tools' ); ?></h4>
                        <p><?php esc_html_e( 'Single or multi-line text fields for names, descriptions, notes.', 'disciple_tools' ); ?></p>
                        <div class="dt-import-example">
                            <strong><?php esc_html_e( 'Examples:', 'disciple_tools' ); ?></strong>
                            <code>John Smith</code>, <code>A new contact from the outreach event</code>
                        </div>
                    </div>
                    
                    <div class="dt-import-field-type">
                        <h4><?php esc_html_e( 'Number', 'disciple_tools' ); ?></h4>
                        <p><?php esc_html_e( 'Numeric values (integers or decimals).', 'disciple_tools' ); ?></p>
                        <div class="dt-import-example">
                            <strong><?php esc_html_e( 'Examples:', 'disciple_tools' ); ?></strong>
                            <code>25</code>, <code>8.5</code>, <code>-2.7</code>
                        </div>
                    </div>
                    
                    <div class="dt-import-field-type">
                        <h4><?php esc_html_e( 'Date', 'disciple_tools' ); ?></h4>
                        <p><?php esc_html_e( 'Date values in various formats.', 'disciple_tools' ); ?></p>
                        <div class="dt-import-example">
                            <strong><?php esc_html_e( 'Accepted Formats:', 'disciple_tools' ); ?></strong><br>
                            <code>2024-01-15</code> <?php esc_html_e( '(recommended)', 'disciple_tools' ); ?><br>
                            <code>01/15/2024</code>, <code>15/01/2024</code><br>
                            <code>January 15, 2024</code>
                        </div>
                    </div>
                    
                    <div class="dt-import-field-type">
                        <h4><?php esc_html_e( 'Boolean', 'disciple_tools' ); ?></h4>
                        <p><?php esc_html_e( 'True/false values with flexible formatting.', 'disciple_tools' ); ?></p>
                        <div class="dt-import-example">
                            <strong><?php esc_html_e( 'TRUE values:', 'disciple_tools' ); ?></strong> <code>true</code>, <code>yes</code>, <code>y</code>, <code>1</code>, <code>on</code><br>
                            <strong><?php esc_html_e( 'FALSE values:', 'disciple_tools' ); ?></strong> <code>false</code>, <code>no</code>, <code>n</code>, <code>0</code>, <code>off</code>
                        </div>
                    </div>
                    
                    <div class="dt-import-field-type">
                        <h4><?php esc_html_e( 'Dropdown (Key Select)', 'disciple_tools' ); ?></h4>
                        <p><?php esc_html_e( 'Single selection from predefined options like status, type, seeker path.', 'disciple_tools' ); ?></p>
                        <div class="dt-import-example">
                            <strong><?php esc_html_e( 'Contact Status:', 'disciple_tools' ); ?></strong> <code>new</code>, <code>active</code>, <code>paused</code>, <code>closed</code><br>
                            <strong><?php esc_html_e( 'Seeker Path:', 'disciple_tools' ); ?></strong> <code>none</code>, <code>attempted</code>, <code>ongoing</code>, <code>coaching</code>
                        </div>
                    </div>
                    
                    <div class="dt-import-field-type">
                        <h4><?php esc_html_e( 'Multi-Select', 'disciple_tools' ); ?></h4>
                        <p><?php esc_html_e( 'Multiple selections separated by semicolons.', 'disciple_tools' ); ?></p>
                        <div class="dt-import-example">
                            <strong><?php esc_html_e( 'Sources:', 'disciple_tools' ); ?></strong> <code>web;referral</code><br>
                            <strong><?php esc_html_e( 'Milestones:', 'disciple_tools' ); ?></strong> <code>milestone_has_bible;milestone_reading_bible</code>
                        </div>
                    </div>
                    
                    <div class="dt-import-field-type">
                        <h4><?php esc_html_e( 'Communication Channels', 'disciple_tools' ); ?></h4>
                        <p><?php esc_html_e( 'Phone numbers, emails, and social media contacts.', 'disciple_tools' ); ?></p>
                        <div class="dt-import-example">
                            <strong><?php esc_html_e( 'Email:', 'disciple_tools' ); ?></strong> <code>john@example.com;backup@example.com</code><br>
                            <strong><?php esc_html_e( 'Phone:', 'disciple_tools' ); ?></strong> <code>555-123-4567;555-987-6543</code>
                        </div>
                    </div>
                    
                    <div class="dt-import-field-type">
                        <h4><?php esc_html_e( 'Connections', 'disciple_tools' ); ?></h4>
                        <p><?php esc_html_e( 'Relationships to other records (contacts or groups).', 'disciple_tools' ); ?></p>
                        <div class="dt-import-example">
                            <strong><?php esc_html_e( 'By Name:', 'disciple_tools' ); ?></strong> <code>John Smith;Mary Johnson</code><br>
                            <strong><?php esc_html_e( 'By ID:', 'disciple_tools' ); ?></strong> <code>142;256</code>
                        </div>
                    </div>
                    
                    <div class="dt-import-field-type">
                        <h4><?php esc_html_e( 'Locations', 'disciple_tools' ); ?></h4>
                        <p><?php esc_html_e( 'Geographic information in various formats.', 'disciple_tools' ); ?></p>
                        <div class="dt-import-example">
                            <strong><?php esc_html_e( 'Address:', 'disciple_tools' ); ?></strong> <code>123 Main St, Springfield, IL</code><br>
                            <strong><?php esc_html_e( 'Decimal Coordinates:', 'disciple_tools' ); ?></strong> <code>40.7128,-74.0060</code><br>
                            <strong><?php esc_html_e( 'DMS Coordinates:', 'disciple_tools' ); ?></strong> <code>35°50′40.9″N, 103°27′7.5″E</code><br>
                            <strong><?php esc_html_e( 'Multiple Locations:', 'disciple_tools' ); ?></strong> <code>Paris, France; Berlin, Germany</code><br>
                            <strong><?php esc_html_e( 'Grid ID:', 'disciple_tools' ); ?></strong> <code>100364199</code>
                        </div>
                    </div>
                </div>
                
                <!-- Examples Tab -->
                <div id="examples" class="dt-import-docs-tab-content">
                    <h3><?php esc_html_e( 'CSV Examples', 'disciple_tools' ); ?></h3>
                    
                    <div class="dt-import-example-section">
                        <h4><?php esc_html_e( 'Basic Contact Import', 'disciple_tools' ); ?></h4>
                        <div class="dt-import-csv-example">
                            <table class="dt-import-example-table">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'Name', 'disciple_tools' ); ?></th>
                                        <th><?php esc_html_e( 'Email', 'disciple_tools' ); ?></th>
                                        <th><?php esc_html_e( 'Phone', 'disciple_tools' ); ?></th>
                                        <th><?php esc_html_e( 'Status', 'disciple_tools' ); ?></th>
                                        <th><?php esc_html_e( 'Source', 'disciple_tools' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>John Smith</td>
                                        <td>john@example.com</td>
                                        <td>555-123-4567</td>
                                        <td>new</td>
                                        <td>web</td>
                                    </tr>
                                    <tr>
                                        <td>Mary Johnson</td>
                                        <td>mary@example.com</td>
                                        <td>555-987-6543</td>
                                        <td>active</td>
                                        <td>referral</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="dt-import-example-section">
                        <h4><?php esc_html_e( 'Multi-Value Fields Example', 'disciple_tools' ); ?></h4>
                        <p><?php esc_html_e( 'Use semicolons to separate multiple values:', 'disciple_tools' ); ?></p>
                        <div class="dt-import-csv-example">
                            <table class="dt-import-example-table">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'Name', 'disciple_tools' ); ?></th>
                                        <th><?php esc_html_e( 'Sources', 'disciple_tools' ); ?></th>
                                        <th><?php esc_html_e( 'Milestones', 'disciple_tools' ); ?></th>
                                        <th><?php esc_html_e( 'Groups', 'disciple_tools' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>John Smith</td>
                                        <td>web;referral</td>
                                        <td>milestone_has_bible;milestone_reading_bible</td>
                                        <td>Bible Study;Prayer Group</td>
                                    </tr>
                                    <tr>
                                        <td>Mary Johnson</td>
                                        <td>facebook</td>
                                        <td>milestone_belief;milestone_baptized</td>
                                        <td>Youth Group</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="dt-import-example-section">
                        <h4><?php esc_html_e( 'Location Formats', 'disciple_tools' ); ?></h4>
                        
                        <h5><?php esc_html_e( 'Coordinate Formats', 'disciple_tools' ); ?></h5>
                        <p><?php esc_html_e( 'Multiple coordinate formats are supported:', 'disciple_tools' ); ?></p>
                        <div class="dt-import-csv-example">
                            <table class="dt-import-example-table">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'Name', 'disciple_tools' ); ?></th>
                                        <th><?php esc_html_e( 'Location', 'disciple_tools' ); ?></th>
                                        <th><?php esc_html_e( 'Format', 'disciple_tools' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>New York</td>
                                        <td>40.7128,-74.0060</td>
                                        <td>Decimal Degrees</td>
                                    </tr>
                                    <tr>
                                        <td>Beijing</td>
                                        <td>39°54′26″N, 116°23′29″E</td>
                                        <td>DMS Coordinates</td>
                                    </tr>
                                    <tr>
                                        <td>Multiple</td>
                                        <td>Paris, France; Berlin, Germany</td>
                                        <td>Multiple Addresses</td>
                                    </tr>
                                    <tr>
                                        <td>Grid Location</td>
                                        <td>100364199</td>
                                        <td>Location Grid ID</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <h5><?php esc_html_e( 'Text with Commas', 'disciple_tools' ); ?></h5>
                        <p><?php esc_html_e( 'Use quotes around text containing commas:', 'disciple_tools' ); ?></p>
                        <div class="dt-import-csv-example">
                            <table class="dt-import-example-table">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'Name', 'disciple_tools' ); ?></th>
                                        <th><?php esc_html_e( 'Address', 'disciple_tools' ); ?></th>
                                        <th><?php esc_html_e( 'Notes', 'disciple_tools' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>John Smith</td>
                                        <td>"123 Main St, Springfield, IL"</td>
                                        <td>"Met at coffee shop, very interested"</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <h5><?php esc_html_e( 'Empty Values', 'disciple_tools' ); ?></h5>
                        <p><?php esc_html_e( 'Leave cells empty if no data is available:', 'disciple_tools' ); ?></p>
                        <div class="dt-import-csv-example">
                            <table class="dt-import-example-table">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'Name', 'disciple_tools' ); ?></th>
                                        <th><?php esc_html_e( 'Phone', 'disciple_tools' ); ?></th>
                                        <th><?php esc_html_e( 'Email', 'disciple_tools' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>John Smith</td>
                                        <td></td>
                                        <td>john@example.com</td>
                                    </tr>
                                    <tr>
                                        <td>Jane Doe</td>
                                        <td>555-123-4567</td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Troubleshooting Tab -->
                <div id="troubleshooting" class="dt-import-docs-tab-content">
                    <h3><?php esc_html_e( 'Common Issues & Solutions', 'disciple_tools' ); ?></h3>
                    
                    <div class="dt-import-troubleshoot-item">
                        <h4><?php esc_html_e( '"Invalid number" Error', 'disciple_tools' ); ?></h4>
                        <p><strong><?php esc_html_e( 'Solution:', 'disciple_tools' ); ?></strong> <?php esc_html_e( 'Ensure numeric fields contain only numbers. Remove any text, spaces, or special characters.', 'disciple_tools' ); ?></p>
                    </div>
                    
                    <div class="dt-import-troubleshoot-item">
                        <h4><?php esc_html_e( '"Invalid date format" Error', 'disciple_tools' ); ?></h4>
                        <p><strong><?php esc_html_e( 'Solution:', 'disciple_tools' ); ?></strong> <?php esc_html_e( 'Use YYYY-MM-DD format (e.g., 2024-01-15) or common formats like 01/15/2024.', 'disciple_tools' ); ?></p>
                    </div>
                    
                    <div class="dt-import-troubleshoot-item">
                        <h4><?php esc_html_e( '"Invalid email address" Error', 'disciple_tools' ); ?></h4>
                        <p><strong><?php esc_html_e( 'Solution:', 'disciple_tools' ); ?></strong> <?php esc_html_e( 'Check email format - must contain @ and valid domain. Example: user@domain.com', 'disciple_tools' ); ?></p>
                    </div>
                    
                    <div class="dt-import-troubleshoot-item">
                        <h4><?php esc_html_e( '"Invalid option for field" Error', 'disciple_tools' ); ?></h4>
                        <p><strong><?php esc_html_e( 'Solution:', 'disciple_tools' ); ?></strong> <?php esc_html_e( 'Check that dropdown values match available options exactly. Use the field mapping step to see valid options.', 'disciple_tools' ); ?></p>
                    </div>
                    
                    <div class="dt-import-troubleshoot-item">
                        <h4><?php esc_html_e( '"Connection not found" Error', 'disciple_tools' ); ?></h4>
                        <p><strong><?php esc_html_e( 'Solution:', 'disciple_tools' ); ?></strong> <?php esc_html_e( 'Ensure connected records exist. Use exact names or valid record IDs.', 'disciple_tools' ); ?></p>
                    </div>
                    
                    <div class="dt-import-troubleshoot-item">
                        <h4><?php esc_html_e( '"Invalid DMS coordinates" Error', 'disciple_tools' ); ?></h4>
                        <p><strong><?php esc_html_e( 'Solution:', 'disciple_tools' ); ?></strong> <?php esc_html_e( 'Ensure DMS coordinates include direction indicators (N/S/E/W) and are properly formatted. Example: 35°50′40.9″N, 103°27′7.5″E', 'disciple_tools' ); ?></p>
                    </div>
                    
                    <div class="dt-import-troubleshoot-item">
                        <h4><?php esc_html_e( 'File Upload Issues', 'disciple_tools' ); ?></h4>
                        <p><strong><?php esc_html_e( 'Solutions:', 'disciple_tools' ); ?></strong></p>
                        <ul>
                            <li><?php esc_html_e( 'Ensure file is .csv format and under 10MB', 'disciple_tools' ); ?></li>
                            <li><?php esc_html_e( 'Save file with UTF-8 encoding', 'disciple_tools' ); ?></li>
                            <li><?php esc_html_e( 'Check that all rows have the same number of columns', 'disciple_tools' ); ?></li>
                        </ul>
                    </div>
                    
                    <h3><?php esc_html_e( 'Best Practices', 'disciple_tools' ); ?></h3>
                    <ol>
                        <li><strong><?php esc_html_e( 'Start Small:', 'disciple_tools' ); ?></strong> <?php esc_html_e( 'Test with 5-10 records first', 'disciple_tools' ); ?></li>
                        <li><strong><?php esc_html_e( 'Use Examples:', 'disciple_tools' ); ?></strong> <?php esc_html_e( 'Download and modify the provided example CSV files', 'disciple_tools' ); ?></li>
                        <li><strong><?php esc_html_e( 'Check Options:', 'disciple_tools' ); ?></strong> <?php esc_html_e( 'Review available options for dropdown fields during mapping', 'disciple_tools' ); ?></li>
                        <li><strong><?php esc_html_e( 'Clean Data:', 'disciple_tools' ); ?></strong> <?php esc_html_e( 'Remove extra spaces and validate data before import', 'disciple_tools' ); ?></li>
                        <li><strong><?php esc_html_e( 'Backup First:', 'disciple_tools' ); ?></strong> <?php esc_html_e( 'Always backup your data before large imports', 'disciple_tools' ); ?></li>
                    </ol>
                </div>
            </div>
        </div>
        
        <div class="dt-import-modal-footer">
            <button type="button" class="button button-secondary" id="dt-import-docs-close-btn">
                <?php esc_html_e( 'Close', 'disciple_tools' ); ?>
            </button>
        </div>
    </div>
</div> 