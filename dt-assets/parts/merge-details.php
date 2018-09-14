<?php

( function () {

    function array_contains(array $array, string $string) {
        foreach($array as $item) {
            if(is_array($item)) {
                if(array_contains($item, $string)) {
                    return true;
                }
            } elseif(!is_object($item)) {
                if(strpos($item, $string) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    $contact = Disciple_Tools_Contacts::get_contact( get_the_ID(), true );
    $channel_list = Disciple_Tools_Contacts::get_channel_list();
    $current_user = wp_get_current_user();
    $contact_fields = Disciple_Tools_Contacts::get_contact_fields();

    ?>
    <div class="reveal" id="merge-dupe-edit" style="border-radius:10px; padding:0px; padding-bottom:20px; border: 1px solid #3f729b;;" data-reveal>
      <div class="merge-modal-header" style="background-color:#3f729b; color:white; text-align:center;">
        <h1 style="font-size:1.5rem; padding:10px 0px;"><?php esc_html_e( "Duplicate Contacts", 'disciple_tools' ) ?></h1>
      </div>
        <div class="display-fields" style="padding:10px;">
          </div>

                

        <form action="<?php echo esc_url( site_url( '/contacts/' . get_the_ID() ) ); ?>" id='form-dismiss' method="POST">
            <input type='hidden' name='dt_contact_nonce' value="<?php echo esc_attr( wp_create_nonce() ); ?>">
            <input type='hidden' name='dismiss' value='1'/>
            <input type='hidden' id="dismiss-id" name='id' value='<?php echo esc_html( $value ); ?>'/>
            <input type='hidden' name='currentId' value='<?php echo get_the_ID(); ?>'/>
            <input type='submit' style='display: none' value='Dismiss'/>
        </form>
        <form action="<?php echo esc_url( site_url( '/contacts/' . get_the_ID() ) ); ?>" id='form-unsure' method="POST">
            <input type='hidden' name='dt_contact_nonce' value="<?php echo esc_attr( wp_create_nonce() ); ?>">
            <input type='hidden' name='unsure' value='1'/>
            <input type='hidden' id="unsure-id" name='id' value='<?php echo esc_html( $value ); ?>'/>
            <input type='hidden' name='currentId' value='<?php echo get_the_ID(); ?>'/>
            <input type='submit' style='display: none' value='Unsure'/>
        </form>
        </div>
    </div>

    <script type='text/javascript'>
        loadDuplicates();
        function loadDuplicates() {
            var template_dir = "<?php echo esc_url( get_template_directory_uri() ); ?>";
            var site_url = "<?php echo esc_url(site_url());?>";
            var contact_id = "<?php echo get_the_ID(); ?>";

            $display_fields = $(".display-fields");
            $display_fields.append("<h4 style='text-align: center; font-size: 1.25rem; font-weight: bold; padding:10px 0px 0px; margin-bottom: 0px;'>Original Contact</h4>");
            API.get_post('contact', contact_id).done(function(response) {
                var contact = response;
                var duplicates = contact.duplicate_data;
                var dupes = [];
                var fields = {};
                var original_contact_html = "<div style='background-color: #e1f5fe; padding:2%'><h5 style='font-weight: bold; color: #3f729b;'>" + contact.title + "</h5>";
                $.each(contact, function(key, vals) {
                    if(!key.match(/^contact_/)) { return true; }
                    if(!fields[key]) {
                        fields[key] = [];
                    }
                    $.each(vals, function(k, val) {
                        fields[key].push(val.value);
                        switch(key) {
                            case 'contact_phone':
                                var svg = 'phone.svg';
                                break;
                            case 'contact_address':
                                var svg = 'house.svg';
                                break;
                            case 'contact_email':
                                var svg = 'email.svg';
                                break;
                            case 'contact_facebook':
                                var svg = 'facebook.svg';
                                break;
                            case 'contact_twitter':
                                var svg = 'twitter.svg';
                                break;
                            default:
                                return true; //skip
                        }
                        original_contact_html += "<img src='" + template_dir + "/dt-assets/images/" + svg + "'>&nbsp;" + val.value + "<br>";
                    });
                });
                original_contact_html += "</div>";

                $display_fields.append(original_contact_html);

                $display_fields.append("<div id='duplicates_list'></div><div id='unsure_list'></div>");

                $.each(duplicates, function(key, vals) {
                    if(key !== 'override' && key !== 'unsure') {
                        $.each(vals, function(k, val) {
                            if(!dupes.includes(val)) {
                                dupes.push(val);
                            }
                        });
                    }
                });

                if(dupes.length) {
                    $duplicates = $display_fields.find('#duplicates_list');
                    $duplicates.append("<h4 style='text-align: center; font-size: 1.25rem; font-weight: bold; padding:20px 0px 0px; margin-bottom: 0px;'>Possible Duplicates</h4>");

                    var unsure_dismiss_html = "<div style='display:inline-block; width: 100%;'><form method='POST' id='form-unsure-dismiss' action='"+site_url+"/contacts/"+contact_id+"'><input type='hidden' name='dt_contact_nonce' value='<?php echo esc_attr(wp_create_nonce());?>'/><input type='hidden' name='id' value='<?php echo get_the_ID(); ?>'/><a style='float: right; margin-left: 10%;' onclick='dismiss_all();'>Dismiss All</a><a style='float: right;' onclick='unsure_all();'>Unsure All</a><input type='submit' id='unsure-dismiss-submit' style='display: none;' value='submit'/></form></div>";
                    
                    $duplicates.append(unsure_dismiss_html);

                    $.each(dupes, function(key, id) {
                        API.get_post('contact', id).done(function(dupe) {
                            var duplicate_contact_html = $("<div style='background-color: #f2f2f2; padding:2%; overflow: hidden;'><h5 style='font-weight: bold; color: #3f729b;'></h5>");
                            duplicate_contact_html.find('h5').text(dupe.title);
                            $.each(dupe, function(key, vals) {
                                if(!key.match(/^contact_/)) {
                                    return true;
                                }
                                $.each(vals, function(k, val) {
                                    if(in_array(fields[key], val.value)) {
                                        switch(key) {
                                            case 'contact_phone':
                                                var svg = 'phone.svg';
                                                break;
                                            case 'contact_address':
                                                var svg = 'house.svg';
                                                break;
                                            case 'contact_email':
                                                var svg = 'email.svg';
                                                break;
                                            case 'contact_facebook':
                                                var svg = 'facebook.svg';
                                                break;
                                            case 'contact_twitter':
                                                var svg = 'twitter.svg';
                                                break;
                                            default:
                                                return true; //skip
                                        }
                                        duplicate_contact_html.append("<img src='" + template_dir + "/dt-assets/images/" + svg + "'>&nbsp;" + val.value + "<br>");
                                    }
                                });
                            });
                            duplicate_contact_html.append("<button class='mergelinks' onclick='$(\"#dismiss-id\").val(" + id + "); $(\"#form-dismiss input[type=submit]\").click();' style='float: right; padding-left: 10%;'><a>Dismiss</a></button>");

                            duplicate_contact_html.append("<button class='mergelinks' onclick='$(\"#unsure-id\").val(" + id + "); $(\"#form-unsure input[type=submit]\").click();' style='float: right; padding-left: 10%;'><a>Unsure</a></button>");

                            duplicate_contact_html.append("<form action='"+site_url+"/contacts/mergedetails' method='post'><input type='hidden' name='dt_contact_nonce' value='<?php echo esc_attr(wp_create_nonce()); ?>'/><input type='hidden' name='currentid' value='"+contact_id+"'/><input type='hidden' name='dupeid' value='"+id+"'/><button type='submit' style='float:right; padding-left: 10%;'><a>Merge</a></button></form>");

                            $duplicates.append(duplicate_contact_html);
                        });
                    });
                }

                if(duplicates.unsure) {
                    $unsure = $display_fields.find('#unsure_list');
                    $unsure.append("<h4 style='text-align: center; font-size: 1.25rem; font-weight: bold; padding: 20px 0px 0px; margin-bottom: 0px;'>Unsure Duplicates</h4>");

                    $.each(duplicates.unsure, function(key, id) {
                        API.get_post('contact', id).done(function(dupe) {
                            var unsure_duplicate_html = $("<div style='background-color: #f2f2f2; padding:2%; overflow: hidden;'><h5 style='font-weight: bold; color: #3f729b;'></h5>");
                            unsure_duplicate_html.find('h5').text(dupe.title);
                            $.each(dupe, function(key, vals) {
                                if(!key.match(/^contact_/)) {
                                    return true;
                                }
                                $.each(vals, function(k, val) {
                                    if(in_array(fields[key], val.value)) {
                                        switch(key) {
                                            case 'contact_phone':
                                                var svg = 'phone.svg';
                                                break;
                                            case 'contact_address':
                                                var svg = 'house.svg';
                                                break;
                                            case 'contact_email':
                                                var svg = 'email.svg';
                                                break;
                                            case 'contact_facebook':
                                                var svg = 'facebook.svg';
                                                break;
                                            case 'contact_twitter':
                                                var svg = 'twitter.svg';
                                                break;
                                            default:
                                                return true; //skip
                                        }
                                        unsure_duplicate_html.append("<img src='" + template_dir + "/dt-assets/images/" + svg + "'>&nbsp;" + val.value + "<br>");
                                    }
                                });
                            });
                            unsure_duplicate_html.append("<button class='mergelinks' onclick='$("+'#dismiss-id'+").val(" + id + "); $("+'#form-dimiss input[type=submit]'+").click();' style='float: right; padding-left: 10%;'><a>Dismiss</a></button>");

                            $unsure.append(unsure_duplicate_html);
                        });
                    });
                }
            });
        }

        function in_array(array, string) {
            if(!$.isArray(array)) {
                return false;
            }

            var ret = false;
            $.each(array, function(k, val) {
                if($.isArray(val)) {
                    if(in_array(val, string)) {
                        ret = true;
                        return false; //break loop
                    }
                } else if(typeof val !== 'object' && val != null) {
                    if(val.indexOf(string) !== -1) {
                        ret = true;
                        return false; //break loop
                    }
                }
            });

            return ret;
        }

        function unsure_all() {
            var form = $("#form-unsure-dismiss");
            var submit = form.find('input[type=submit]');
            submit.attr('name', 'unsure_all');
            submit.click();
        }

        function dismiss_all() {
            var form = $("#form-unsure-dismiss");
            var submit = form.find('input[type=submit]');
            submit.attr('name', 'dismiss_all');
            submit.click();
        }
    </script>
<?php } )(); ?>
