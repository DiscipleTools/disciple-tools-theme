<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( !class_exists( 'DT_Storage' ) ) {
    class DT_Storage {

        private static function get_settings(): array {
            // New single-connection flat format
            $connection = get_option( 'dt_storage_connection', [] );
            // Ensure default for path_style: true for minio, false otherwise if not set
            if ( is_array( $connection ) && !array_key_exists( 'path_style', $connection ) ) {
                $connection['path_style'] = ( isset( $connection['type'] ) && $connection['type'] === 'minio' );
            }
            $id = is_array( $connection ) && isset( $connection['id'] ) ? $connection['id'] : '';
            return [ $id, is_array( $connection ) ? $connection : [] ];
        }

        public static function update_default_connection_id( string $connection_id ): bool {
            return update_option( 'dt_storage_connection_id', $connection_id );
        }

        public static function get_default_connection_id(): string {
            $id = dt_get_option( 'dt_storage_connection_id' );
            return !empty( $id ) ? $id : '';
        }

        public static function is_enabled(): bool {
            [ $id, $conn ] = self::get_settings();
            return !empty( $id ) && !empty( $conn ) && !empty( $conn['enabled'] );
        }

        private static function build_client_and_config(): array {
            [ $id, $conn ] = self::get_settings();
            if ( empty( $conn ) || empty( $conn['type'] ) ) {
                return [ null, null, null ];
            }
            $cfg = $conn;
            if ( !isset( $cfg['access_key'], $cfg['secret_access_key'], $cfg['region'], $cfg['bucket'], $cfg['endpoint'] ) ) {
                return [ null, null, null ];
            }
            $endpoint = self::validate_url( $cfg['endpoint'] );
            $class = '\\AsyncAws\\S3\\S3Client';
            if ( !class_exists( $class ) ) {
                return [ null, null, null ];
            }
            $client = new $class([
                'region' => $cfg['region'],
                'endpoint' => $endpoint,
                'accessKeyId' => $cfg['access_key'],
                'accessKeySecret' => $cfg['secret_access_key'],
                'pathStyleEndpoint' => (bool) ( $cfg['path_style'] ?? false ),
            ]);
            return [ $client, $cfg['bucket'], $id ];
        }

        private static function validate_url( $url ): string {
            if ( !filter_var( $url, FILTER_VALIDATE_URL ) ) {
                $http = 'http://';
                $https = 'https://';
                if ( ( substr( $url, 0, strlen( $http ) ) !== $http ) && ( substr( $url, 0, strlen( $https ) ) !== $https ) ) {
                    $url = $https . trim( $url );
                }
            }
            return $url;
        }

        public static function get_file_url( string $key ): string {
            [ $client, $bucket ] = self::build_client_and_config();
            if ( !$client ) {
                return '';
            }
            try {
                $inputClass = '\\AsyncAws\\S3\\Input\\GetObjectRequest';
                if ( class_exists( $inputClass ) ) {
                    $input = new $inputClass( [ 'Bucket' => $bucket, 'Key' => $key ] );
                    $presigned = $client->presign( $input, new \DateTimeImmutable( '+24 hours' ) );
                    return (string) $presigned;
                }
                return '';
            } catch ( Throwable $e ) {
                return '';
            }
        }

        public static function get_thumbnail_url( string $key ): string {
            return self::get_file_url( self::generate_thumbnail_key_name( $key ) );
        }

        public static function get_large_thumbnail_url( string $key ): string {
            return self::get_file_url( self::generate_large_thumbnail_key_name( $key ) );
        }

        public static function upload_file( string $key_prefix = '', array $upload = [], string $existing_key = '', array $args = [] ){
            $key_prefix = trailingslashit( $key_prefix );
            [ $client, $bucket ] = self::build_client_and_config();
            if ( !$client ) {
                return false;
            }

            $auto_key = empty( $existing_key );
            $key = $auto_key ? ( $key_prefix . self::generate_random_string( 64 ) ) : $existing_key;
            if ( $auto_key && !empty( $upload['full_path'] ) ) {
                $ext = pathinfo( $upload['full_path'], PATHINFO_EXTENSION );
                if ( !empty( $ext ) ) {
                    $key .= '.' . $ext;
                }
            }

            $tmp = $upload['tmp_name'] ?? '';
            $type = $upload['type'] ?? '';

            try {
                $client->putObject([
                    'Bucket' => $bucket,
                    'Key' => $key,
                    'Body' => fopen( $tmp, 'r' ),
                    'ContentType' => $type
                ]);

                $uploaded_thumbnail_key = null;
                $uploaded_large_thumbnail_key = null;

                $is_image = in_array( strtolower( trim( $type ) ), [ 'image/gif', 'image/jpeg', 'image/png' ], true );
                if ( $is_image ) {
                    // Small thumb
                    $thumb = self::generate_image_thumbnail( $tmp, $type, 100 );
                    if ( $thumb ) {
                        $thumb_path = self::image_to_temp_file( $thumb, $type );
                        if ( $thumb_path ) {
                            $client->putObject([
                                'Bucket' => $bucket,
                                'Key' => self::generate_thumbnail_key_name( $key ),
                                'Body' => fopen( $thumb_path, 'r' ),
                                'ContentType' => $type
                            ]);
                            @unlink( $thumb_path );
                            $uploaded_thumbnail_key = self::generate_thumbnail_key_name( $key );
                        }
                    }

                    // Large thumb
                    $lthumb = self::generate_image_thumbnail( $tmp, $type, 1200 );
                    if ( $lthumb ) {
                        $lthumb_path = self::image_to_temp_file( $lthumb, $type );
                        if ( $lthumb_path ) {
                            $client->putObject([
                                'Bucket' => $bucket,
                                'Key' => self::generate_large_thumbnail_key_name( $key ),
                                'Body' => fopen( $lthumb_path, 'r' ),
                                'ContentType' => $type
                            ]);
                            @unlink( $lthumb_path );
                            $uploaded_large_thumbnail_key = self::generate_large_thumbnail_key_name( $key );
                        }
                    }
                }

                return [
                'uploaded_key' => $key,
                'uploaded_thumbnail_key' => $uploaded_thumbnail_key,
                'uploaded_large_thumbnail_key' => $uploaded_large_thumbnail_key,
                ];
            } catch ( Throwable $e ) {
                return false;
            }
        }

        public static function delete_file( string $key ) {
            [ $client, $bucket ] = self::build_client_and_config();
            if ( !$client ) {
                return false;
            }
            try {
                $client->deleteObject( [ 'Bucket' => $bucket, 'Key' => $key ] );
                $resp = [ 'file_key' => $key, 'file_deleted' => true ];

                $ext = strtolower( pathinfo( $key, PATHINFO_EXTENSION ) );
                if ( in_array( $ext, [ 'png', 'gif', 'jpeg', 'jpg' ], true ) ) {
                    try {
                        $thumb = self::generate_thumbnail_key_name( $key );
                        $client->deleteObject( [ 'Bucket' => $bucket, 'Key' => $thumb ] );
                        $resp['thumbnail_key'] = $thumb;
                        $resp['thumbnail_deleted'] = true;

                        $lthumb = self::generate_large_thumbnail_key_name( $key );
                        $client->deleteObject( [ 'Bucket' => $bucket, 'Key' => $lthumb ] );
                        $resp['large_thumbnail_key'] = $lthumb;
                        $resp['large_thumbnail_deleted'] = true;
                    } catch ( Throwable $te ) {}
                }
                return $resp;
            } catch ( Throwable $e ) {
                return null;
            }
        }

        private static function image_to_temp_file( $gd_image, string $type ) {
            $path = tempnam( sys_get_temp_dir(), 'dt_img_' );
            if ( $path === false ) {
                return null;
            }
            $ok = false;
            switch ( strtolower( trim( $type ) ) ) {
                case 'image/gif':
                    $ok = imagegif( $gd_image, $path );
                    break;
                case 'image/jpeg':
                    $ok = imagejpeg( $gd_image, $path );
                    break;
                case 'image/png':
                    $ok = imagepng( $gd_image, $path );
                    break;
            }
            if ( !$ok ) {
                @unlink( $path );
                return null;
            }
            return $path;
        }

        private static function generate_random_string( $length = 112 ): string {
            $random_string = '';
            $keys = array_merge( range( 0, 9 ), range( 'a', 'z' ), range( 'A', 'Z' ) );
            for ( $i = 0; $i < $length; $i++ ){
                $random_string .= $keys[mt_rand( 0, count( $keys ) - 1 )];
            }
            return $random_string;
        }

        public static function generate_image_thumbnail( $src, $content_type, $desired_width ) {
            $thumbnail = null;
            try {
                switch ( strtolower( trim( $content_type ) ) ) {
                    case 'image/gif':
                        $source_image = imagecreatefromgif( $src );
                        break;
                    case 'image/jpeg':
                        $source_image = imagecreatefromjpeg( $src );
                        break;
                    case 'image/png':
                        $source_image = imagecreatefrompng( $src );
                        break;
                    default:
                        $source_image = null;
                        break;
                }
                if ( !empty( $source_image ) ) {
                    $width = imagesx( $source_image );
                    $height = imagesy( $source_image );
                    $desired_height = floor( $height * ( $desired_width / $width ) );
                    $virtual_image = imagecreatetruecolor( $desired_width, $desired_height );
                    $black = imagecolorallocate( $virtual_image, 0, 0, 0 );
                    imagecolortransparent( $virtual_image, $black );
                    imagecopyresampled( $virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height );
                    if ( !empty( $virtual_image ) ) {
                        $thumbnail = $virtual_image;
                    }
                }
            } catch ( Exception $e ) {
                $thumbnail = null;
            }
            return $thumbnail;
        }

        public static function generate_thumbnail_key_name( $key_name ): string {
            $thumbnail_key_name = $key_name . '_thumbnail';
            $extension_period_pos = strrpos( $key_name, '.' );
            if ( $extension_period_pos !== false ) {
                $part_1 = substr( $key_name, 0, $extension_period_pos );
                $part_2 = substr( $key_name, $extension_period_pos + 1 );
                $thumbnail_key_name = ( $part_1 . '_thumbnail.' ) . $part_2;
            }
            return $thumbnail_key_name;
        }

        public static function generate_large_thumbnail_key_name( $key_name ): string {
            $large_thumbnail_key_name = $key_name . '_large_thumbnail';
            $extension_period_pos = strrpos( $key_name, '.' );
            if ( $extension_period_pos !== false ) {
                $part_1 = substr( $key_name, 0, $extension_period_pos );
                $part_2 = substr( $key_name, $extension_period_pos + 1 );
                $large_thumbnail_key_name = ( $part_1 . '_large_thumbnail.' ) . $part_2;
            }
            return $large_thumbnail_key_name;
        }
    }
}
