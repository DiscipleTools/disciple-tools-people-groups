<?php

class DT_People_Groups{
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        add_action( 'after_setup_theme', [ $this, 'after_setup_theme' ], 100 );
        add_action( 'p2p_init', [ $this, 'p2p_init' ] );
        add_action( 'dt_details_additional_section', [ $this, 'dt_details_additional_section' ], 10, 2 );

        add_filter( 'dt_custom_fields_settings', [ $this, 'dt_custom_fields_settings' ], 10, 2 );
        add_filter( 'dt_details_additional_section_ids', [ $this, 'dt_details_additional_section_ids' ], 10, 2 );
        add_action( "post_connection_removed", [ $this, "post_connection_removed" ], 10, 4 );
        add_action( "post_connection_added", [ $this, "post_connection_added" ], 10, 4 );
        add_filter( "dt_user_list_filters", [ $this, "dt_user_list_filters" ], 10, 2 );
    }


    public function after_setup_theme(){
        if ( class_exists( 'Disciple_Tools_Post_Type_Template' )) {
            new Disciple_Tools_Post_Type_Template( "peoplegroups", 'People Group', 'People Groups' );
        }
    }

    public function dt_custom_fields_settings( $fields, $post_type ){
        if ( $post_type === 'peoplegroups' ){
            $fields['contact_count'] = [
                'name' => "Number of contacts",
                'type' => 'text',
                'default' => '',
                'show_in_table' => true
            ];
            $fields['group_count'] = [
                'name' => "Number of groups",
                'type' => 'text',
                'default' => '',
                'show_in_table' => true
            ];
            $fields['contacts'] = [
                'name' => "Contacts",
                'type' => 'connection',
                "p2p_direction" => "to",
                "p2p_key" => "contacts_to_peoplegroups",
                'p2p_listing' => 'contacts'
            ];
            $fields['groups'] = [
                'name' => "Groups",
                'type' => 'connection',
                "p2p_direction" => "to",
                "p2p_key" => "groups_to_peoplegroups",
                'p2p_listing' => 'groups'
            ];
            $fields["location_grid"] = [
                'name' => "Locations",
                'type' => 'location',
                'default' => [],
                'show_in_table' => true
            ];
        }
        return $fields;
    }

    public function p2p_init(){
        p2p_register_connection_type([
            'name' => 'peoplegroups_to_contacts',
            'from' => 'peoplegroups',
            'to' => 'contacts'
        ]);
        p2p_register_connection_type([
            'name' => 'peoplegroups_to_groups',
            'from' => 'peoplegroups',
            'to' => 'groups'
        ]);
    }

    public function dt_details_additional_section_ids( $sections, $post_type = "" ){
        if ( $post_type === "peoplegroups"){
            $sections[] = 'contacts';
            $sections[] = 'groups';
        }
        return $sections;
    }

    /**
     * Add people group fields
     */
    public function dt_details_additional_section( $section, $post_type ){
        if ( $post_type === "peoplegroups" ) {
            $post_settings = apply_filters( "dt_get_post_type_settings", [], $post_type );
            $dt_post = DT_Posts::get_post( $post_type, get_the_ID() );

            if ( $section == "contacts" ) {
                ?>
                <label class="section-header">
                    <?php esc_html_e( 'Contacts', 'disciple_tools' ) ?>
                </label>
                <?php
                render_field_for_display( 'contact_count', $post_settings["fields"], $dt_post );
                render_field_for_display( 'contacts', $post_settings["fields"], $dt_post );
            }

            if ( $section == "groups" ) {
                ?>
                <label class="section-header">
                    <?php esc_html_e( 'Groups', 'disciple_tools' ) ?>
                </label>
                <?php
                render_field_for_display( 'group_count', $post_settings["fields"], $dt_post );
                render_field_for_display( 'groups', $post_settings["fields"], $dt_post );
            }

            if ( $section == "details" ) {
                render_field_for_display( 'location_grid', $post_settings["fields"], $dt_post );
            }
        }
    }

    private function update_people_group_counts( $people_group_id, $action = "added", $type = 'contacts' ){
        $people_group = get_post( $people_group_id );
        if ( $type === 'contacts' ){
            $args = [
                'connected_type'   => "contacts_to_peoplegroups",
                'connected_direction' => 'to',
                'connected_items'  => $people_group,
                'nopaging'         => true,
                'suppress_filters' => false,
            ];
            $contacts = get_posts( $args );
            $contact_count = get_post_meta( $people_group_id, 'contact_count', true );
            if ( sizeof( $contacts ) > intval( $contact_count ) ){
                update_post_meta( $people_group_id, 'contact_count', sizeof( $contacts ) );
            } elseif ( $action === "removed" ){
                update_post_meta( $people_group_id, 'contact_count', intval( $contact_count ) - 1 );
            }
        }
        if ( $type === 'groups' ){
            $args = [
                'connected_type'   => "groups_to_peoplegroups",
                'connected_direction' => 'to',
                'connected_items'  => $people_group,
                'nopaging'         => true,
                'suppress_filters' => false,
            ];
            $groups = get_posts( $args );
            $group_count = get_post_meta( $people_group_id, 'group_count', true );
            if ( sizeof( $groups ) > intval( $group_count ) ){
                update_post_meta( $people_group_id, 'group_count', sizeof( $groups ) );
            } elseif ( $action === "removed" ){
                update_post_meta( $people_group_id, 'group_count', intval( $group_count ) - 1 );
            }
        }
    }

    public function post_connection_added( $post_type, $post_id, $post_key, $value ){
        if ( $post_key === 'people_groups' ){
            $this->update_people_group_counts( $value, 'added', $post_type );
        } elseif ( $post_type === 'peoplegroups' ){
            $this->update_people_group_counts( $post_id, 'added', $post_key );
        }
    }
    public function post_connection_removed( $post_type, $post_id, $post_key, $value ){
        if ( $post_key === 'people_groups' ){
            $this->update_people_group_counts( $value, 'removed', $post_type );
        } elseif ( $post_type === 'peoplegroups' ){
            $this->update_people_group_counts( $post_id, 'removed', $post_key );
        }
    }

    public static function dt_user_list_filters( $filters, $post_type ) {
        if ( $post_type === 'peoplegroups' ) {
            $filters["tabs"][] = [
                "key" => "all_people_groups",
                "label" => _x( "All", 'List Filters', 'disciple_tools' ),
                "order" => 10
            ];
            // add assigned to me filters
            $filters["filters"][] = [
                'ID' => 'all_people_groups',
                'tab' => 'all_people_groups',
                'name' => _x( "All", 'List Filters', 'disciple_tools' ),
                'query' => [],
            ];
        }
        return $filters;
    }

}
