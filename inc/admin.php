<?php
class ARP_Admin
{

    public function __construct() {
        add_action( 'admin_menu', array($this, 'register_menu') );
    }


    /**
     * Main Menu
     */
    public function register_menu() {
        add_menu_page(
            __( 'Auto Rotate Lite', ARP__TEXT_DOMAIN ),
            __( 'Auto Rotate Lite', ARP__TEXT_DOMAIN ),
            'manage_options',
            'arp-page',
            array($this, 'main_page'),
            'dashicons-clock',
            26
        );
    }

    /**
     * Child Menu
     */
    public function register_child_menu() {

    }

    /**
     * Main Page
     */
    public function main_page() { ?>

        <h1>
            <?php esc_html_e( 'Auto Rotate Post Plugin Page', ARP__TEXT_DOMAIN ); ?>
        </h1>

        <?php
        $paged = isset( $_GET['paged'] ) ? abs( (int) $_GET['paged'] ) : 1;
        $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'list';
        $ruleset_id = isset( $_GET[ 'ruleset' ] ) ? $_GET[ 'ruleset' ] : 0;
        $items_per_page = max(get_option( 'posts_per_page' ), 25);
        $rotator = new Rotator;

        // INSERT RULESET
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $rotator->getRulesets();
            $total = $result['total'];
            $update = isset($_GET['update']) ? true : false;
            if ( $update || $total <= 3 ) {
                $tags = isset( $_POST['tag_id'] ) ? $_POST['tag_id'] : '';
                $categories = isset( $_POST['category_id'] ) ? $_POST['category_id'] : '';

                $rotator->name = isset( $_POST['name'] ) ? substr($_POST['name'], 0, 64) : '';
                $rotator->post_age = isset( $_POST['post_age'] ) ? $_POST['post_age'] : '';
                $rotator->item = 15;
                $rotator->schedule = 3;
                if ($update && isset($_POST['ruleset_id'])) {
                    $rotator->createRuleset($_POST['ruleset_id']);
                } else {
                    $rotator->createRuleset();
                }
            }
        }

        if ($active_tab === 'list') {
            $rotator->paged = $paged;
            $rotator->items_per_page = $items_per_page;
            $result = $rotator->getRulesets();
            $results = $result['results'];
            $total = $result['total'];
        }

        if ($active_tab === 'log') {
            $rotator->paged = $paged;
            $rotator->items_per_page = $items_per_page;
            $result = $rotator->getLogs();
            $results = $result['results'];
            $total = $result['total'];
        }

        if ($active_tab === 'add') {
            $result = $rotator->getRulesets();
            $total = $result['total'];
        }

        $post_tags = get_terms( array(
            'taxonomy' => 'post_tag',
            'hide_empty' => false,
        ) );

        $categories = get_terms( array(
            'taxonomy' => 'category',
            'hide_empty' => false,
        ) );
        ?>

        <h2 class="nav-tab-wrapper">
            <a href="?page=arp-page&tab=list" class="nav-tab <?php echo ($active_tab === 'list' || $active_tab === 'edit') ? 'nav-tab-active' : ''; ?>"><?php _e( 'All Rules', ARP__TEXT_DOMAIN ); ?>
            </a>

            <a href="?page=arp-page&tab=add" class="nav-tab <?php echo $active_tab === 'add' ? 'nav-tab-active' : ''; ?>">
                <?php _e( 'Add Rule Set', ARP__TEXT_DOMAIN ); ?>
            </a>

            <a href="?page=arp-page&tab=log" class="nav-tab <?php echo $active_tab === 'log' ? 'nav-tab-active' : ''; ?>">
                <?php _e( 'Logs', ARP__TEXT_DOMAIN ); ?>
            </a>
        </h2>

        <br />
        
        <?php if ( $active_tab === 'list' ) : ?>

            <table class="wp-list-table widefat fixed striped posts">
                <thead>
                    <tr>
                        <td><?php _e( 'Rule Name', ARP__TEXT_DOMAIN ); ?></td>
                        <td><?php _e( 'Post Age (in days)', ARP__TEXT_DOMAIN ); ?></td>
                        <td><?php _e( 'Post Qty', ARP__TEXT_DOMAIN ); ?></td>
                        <td><?php _e( 'Schedule (every x days)', ARP__TEXT_DOMAIN ); ?></td>
                        <td><?php _e( 'Date Created', ARP__TEXT_DOMAIN ); ?></td>
                        <td><?php _e( 'Action', ARP__TEXT_DOMAIN ); ?></td>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ( $results as $item ) : ?>
                        <tr id="ruleset-<?php echo $item->id; ?>">
                            <td><?php echo $item->name; ?></td>
                            <td><?php echo $item->post_age; ?></td>
                            <td><?php echo $item->item; ?></td>
                            <td><?php echo $item->schedule; ?></td>
                            <td><?php echo $item->date_created; ?></td>

                            <td>
                                <a href="?page=arp-page&tab=edit&ruleset=<?php echo $item->id; ?>" id="arp-edit" data-id="<?php echo $item->id; ?>" class="button button-link-edit">
                                    <?php _e( 'Edit', ARP__TEXT_DOMAIN ); ?>
                                </a>

                                <button type="button" id="arp-delete" data-id="<?php echo $item->id; ?>" class="button button-link-delete">
                                    <?php _e( 'Delete', ARP__TEXT_DOMAIN ); ?>
                                </button>

                                <!-- <button type="button" id="arp-run" data-id="<?php echo $item->id; ?>"
                                    data-item="<?php echo $item->item; ?>"
                                    data-schedule="<?php echo $item->schedule; ?>"
                                    data-keyword="<?php echo $item->keyword; ?>"
                                    data-tag-id="<?php echo $item->tag_id; ?>"
                                    data-category-id="<?php echo $item->category_id; ?>"
                                    data-post-age="<?php echo $item->post_age; ?>"
                                    class="button">
                                    <?php _e( 'Run Now', ARP__TEXT_DOMAIN ); ?>
                                </button> -->
                                <?php if(!$item->paused): ?>
                                <button type="button" id="arp-pause" data-id="<?php echo $item->id; ?>"
                                    class="button button-link-delete">
                                    <?php _e( 'Pause', ARP__TEXT_DOMAIN ); ?>
                                </button>
                                <?php else: ?>
                                <button type="button" id="arp-resume" data-id="<?php echo $item->id; ?>"
                                    class="button button-link-delete">
                                    <?php _e( 'Resume', ARP__TEXT_DOMAIN ); ?>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ( ! $results ) : ?>
                <p><?php _e('Currently empty'); ?></p>
                <a href="?page=arp-page&tab=add" class="button"><?php _e('Create Rule Set'); ?></a>
            <?php endif; ?>

            <div class="arp-pagination">
                <?php
                echo paginate_links( array(
                    'base' => add_query_arg( 'paged', '%#%' ),
                    'format' => '',
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'total' => ceil($total / $items_per_page),
                    'current' => $paged
                ));
                ?>
            </div>
        
        <?php elseif ( $active_tab === 'add' || $active_tab === 'edit' ) : ?>

            <?php if ($active_tab === 'add' && $total >= 3 ) : ?>
                <p><?php _e('Lite version allow 3 Rule set. Want more Rule set? Purchase Pro version.'); ?></p>
            <?php return; endif; ?>

            <?php 
                if ($active_tab === 'edit' && !empty($ruleset_id)) {
                    $result = $rotator->getRulesets($ruleset_id);
                    if (empty($result)) {
                        ?>
                        <h4>Ruleset Not Found</h4>
                        <a href="?page=arp-page&tab=list" class="button"><?php _e('Back'); ?></a>
                        <?php
                        return;
                    }
                    $ruleset = $result[0];
                    $formAction = '?page=arp-page&update=1';
                } elseif ($active_tab === 'edit' && empty($ruleset_id)) {
                    ?>
                    <h4>No Ruleset Specified</h4>
                    <a href="?page=arp-page&tab=list" class="button"><?php _e('Back'); ?></a>
                    <?php                    
                    return;
                } else {
                    $formAction = '?page=arp-page';
                    $ruleset = new Rotator;
                }
            ?>
            <form method="post" action="<?php echo $formAction ?>">
                <?php if (isset($ruleset)): ?>
                <input type="hidden" name="ruleset_id" value="<?php echo $ruleset->id ?>">
                <?php endif; ?>
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><label for="rule-name"><?php _e( 'Rule Name', ARP__TEXT_DOMAIN ); ?></label></th>
                            <td><input name="name" type="text" id="rule-name" class="regular-text" required value="<?php echo $ruleset->name ?>">
                            <p class="description"><?php _e( 'Name for the ruleset.', ARP__TEXT_DOMAIN ); ?></p></td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="post-age"><?php _e( 'Post Age (in days)', ARP__TEXT_DOMAIN ); ?></label></th>
                            <td><input name="post_age" type="number" id="post-age" class="regular-text" required value="<?php echo $ruleset->post_age ?>">
                            <p class="description"><?php _e( 'All post match this age will be refreshed.', ARP__TEXT_DOMAIN ); ?></p></td>
                        </tr>

                        <?php if ( $categories ) : ?>
                            <tr>
                                <th scope="row"><label for="category-id"><?php _e( 'Category ID', ARP__TEXT_DOMAIN ); ?></label></th>
                                <td>
                                    <?php foreach( $categories as $cat ) : ?>
                                        <p>
                                            <label>
                                                <input disabled="disabled" type="checkbox" id="category-id-<?php echo $cat->term_id; ?>" name="category_id[]" value="<?php echo $cat->term_id; ?>">
                                                <?php echo $cat->name; ?>
                                            </label>
                                        </p>
                                    <?php endforeach; ?>

                                    <p class="description"><?php _e( 'Select categories. Only Available in Pro Version', ARP__TEXT_DOMAIN ); ?></p>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php if ( $post_tags ) : ?>
                            <tr>
                                <th scope="row"><label for="tag-id"><?php _e( 'Tag ID', ARP__TEXT_DOMAIN ); ?></label></th>
                                <td>
                                    <?php foreach( $post_tags as $tag ) : ?>
                                        <p>
                                            <label>
                                                <input disabled="disabled" type="checkbox" id="tag-id-<?php echo $tag->term_id; ?>" name="tag_id[]" value="<?php echo $tag->term_id; ?>">
                                                <?php echo $tag->name; ?>
                                            </label>
                                        </p>
                                    <?php endforeach; ?>

                                    <p class="description"><?php _e( 'Select tags. Only Available in Pro Version', ARP__TEXT_DOMAIN ); ?></p>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <tr>
                            <th scope="row"><label for="keyword"><?php _e( 'Keyword', ARP__TEXT_DOMAIN ); ?></label></th>
                            <td><input disabled="disabled" name="keyword" type="text" id="keyword" class="regular-text" value="<?php echo $ruleset->keyword ?>">
                            <p class="description"><?php _e( 'Separate each id by comma (ex: eat,sleep,run). Only Available in Pro Version', ARP__TEXT_DOMAIN ); ?></p></td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="item"><?php _e( 'Qty', ARP__TEXT_DOMAIN ); ?></label></th>
                            <td><input name="item" type="number" id="item" class="regular-text" min="0" max="25" value="15" disabled="disabled">
                            <p class="description"><?php _e( 'How many post will refreshed. Only Adjustable in Pro Version', ARP__TEXT_DOMAIN ); ?></p></td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="schedule"><?php _e( 'Every (in days)', ARP__TEXT_DOMAIN ); ?></label></th>
                            <td><input name="schedule" type="number" id="item" class="regular-text" required value="3" disabled="disabled">
                            <p class="description"><?php _e( 'In days (ex: if fill 3, will run each 3 days). Only Adjustable in Pro Version', ARP__TEXT_DOMAIN ); ?></p></td>
                        </tr>
                    </tbody>
                </table>
            
                <?php submit_button(); ?>
            </form>

        <?php elseif ( $active_tab === 'log' ) : ?>
            <button type="button" class="button button-link-delete" style="float: right;margin: 0 20px 20px 0" id="clear-log">Clear Logs</button>
            <table class="wp-list-table widefat fixed striped posts">
                <thead>
                    <tr>
                        <td><?php _e( 'Rule set (ID)', ARP__TEXT_DOMAIN ); ?></td>
                        <td><?php _e( 'Run Date', ARP__TEXT_DOMAIN ); ?></td>
                        <td><?php _e( 'Rotated Count', ARP__TEXT_DOMAIN ); ?></td>
                        <td><?php _e( 'Rotated Post ID(s)', ARP__TEXT_DOMAIN ); ?></td>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ( $results as $item ) : ?>
                        <?php
                            if (empty($item->post_ids)) {
                                $postIds = [];
                            } else {
                                $postIds = explode(',', $item->post_ids);
                            }
                            $postList = '';
                            foreach ($postIds as $pid) {
                                $postList .= ",<a href='".get_permalink($pid)."' target='_blank'>".$pid."</a>";
                            }
                        ?>
                        <tr id="ruleset-<?php echo $item->id; ?>">
                            <td><?php echo $item->name . ' (' . $item->ruleset_id . ')'; ?></td>
                            <td><?php echo $item->date_created; ?></td>
                            <td><?php echo count($postIds); ?></td>
                            <td><?php echo substr($postList, 1) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="arp-pagination">
                <?php
                echo paginate_links( array(
                    'base' => add_query_arg( 'paged', '%#%' ),
                    'format' => '',
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'total' => ceil($total / $items_per_page),
                    'current' => $paged
                ));
                ?>
            </div>

        <?php endif;
    }

}

new ARP_Admin();
