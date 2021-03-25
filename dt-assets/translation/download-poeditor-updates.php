<?php


$args = getopt( null, [ "token:", "app-id:" ] );
if ( !isset( $args["token"], $args["app-id"] ) ){
    echo "Missing token or app id";
}

$api_token = $args["token"];
$app_id = $args["app-id"];

$updates_newer_than = time() - 3600 * 24 * 30; // last month
$min_lang_percentage = 70;

/**
 * Get Languages
 * https://poeditor.com/docs/api#languages_list
 */
$ch = curl_init();
curl_setopt( $ch, CURLOPT_URL, "https://api.poeditor.com/v2/languages/list" );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt( $ch, CURLOPT_POST, true );
$data = array(
    'api_token' => $api_token,
    'id' => $app_id,
);
curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
$output = curl_exec( $ch );
curl_close( $ch );
$output = json_decode( $output, true );


if ( !isset( $output["result"]["languages"], $output["response"]["code"] ) || $output["response"]["code"] !== "200" ){
    exit;
}


$lang_codes = [
    "am" => "am_ET",
    "ar-ma" => "ar_MA",
    "bg" => "bg_BG",
    "bn" => "bn_BD",
    "bs" => "bs_BA",
    "es" => "es_ES",
    "es-419" => "es_419",
    "es-ar" => "es_AR",
    "es-co" => "es_CO",
    "es-mx" => "es_MX",
    "fa" => "fa_IR",
    "fr" => "fr_FR",
    "hi" => "hi_IN",
    "mk" => "mk_MK",
    "my" => "my_MM",
    "ne" => "ne_NP",
    "zh-Hans" => "zh_CN",
    "zh-Hant" => "zh_TW",
    "nl" => "nl_NL",
    "en" => "en_US",
    "de" => "de_DE",
    "id" => "id_ID",
    "ko" => "ko_KR",
    "pt-br" => "pt_BR",
    "ru" => "ru_RU",
    "sr" => "sr_BA",
    "sl" => "sl_SI",
    "tr" => "tr_TR",
    "ro" => "ro_RO",
    "pa" => "pa_IN"
];


/**
 * Get .po for recently updated translations
 */
foreach ( $output["result"]["languages"] as $lang ){
    if ( !isset( $lang["updated"], $lang["percentage"], $lang["code"] ) ){
        continue;
    }
    $lang_code = htmlspecialchars( $lang["code"] );
    $file_name = $lang_code;
    if ( isset( $lang_codes[$lang_code] ) ){
        $file_name = $lang_codes[$lang_code];
    }
    $last_updated = strtotime( $lang["updated"] );


    // if translation modified in the last month and progress > 70%
    if ( ( $last_updated > $updates_newer_than ) && $lang["percentage"] > $min_lang_percentage ){

        /**
         * Get translation download link
         * See https://poeditor.com/docs/api#projects_export
         */
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, "https://api.poeditor.com/v2/projects/export" );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_POST, true );
        $data = array(
            'api_token' => $api_token,
            'id' => $app_id,
            'language' => $lang_code,
            'type' => 'po'
        );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
        $download_link_response = curl_exec( $ch );
        curl_close( $ch );
        $download_link_response = json_decode( $download_link_response, true );

        if ( isset( $download_link_response["result"]["url"] ) ){
            $url = $download_link_response["result"]["url"];
            echo( "Downloading " . $lang_code . ".po \r\n" ); //phpcs:ignore
            $success = file_put_contents( $file_name . '.po', file_get_contents( $url ) );
            if ( !$success ){
                echo "error saving .po";
            }
        }
        /**
         * Get translation download link
         */
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, "https://api.poeditor.com/v2/projects/export" );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_POST, true );
        $data = array(
            'api_token' => $api_token,
            'id' => $app_id,
            'language' => $lang_code,
            'type' => 'mo'
        );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
        $download_link_response = curl_exec( $ch );
        curl_close( $ch );
        $download_link_response = json_decode( $download_link_response, true );

        if ( isset( $download_link_response["result"]["url"] ) ){
            $url = $download_link_response["result"]["url"];
            echo( "Downloading " . $lang_code . ".mo \r\n" );//phpcs:ignore
            file_put_contents( $file_name . '.mo', file_get_contents( $url ) );
        }
    }
}

echo "done downloading";
return;

