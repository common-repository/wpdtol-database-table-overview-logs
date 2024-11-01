<?php

/**
 * Plugin name: Database Table Overview and Logs
 * Plugin URI: https://wordpress.org/plugins/wpdtol-database-table-overview-logs/
 * Description: WordPress plugin by 99w.
 * Author: 99w
 * Author URI: https://99w.co.uk
 * Developer: 99w
 * Developer URI: https://99w.co.uk
 * Version: 1.3.0
 * Requires at least: 6.4.0
 * Requires PHP: 7.4.0
 * Requires plugins:
 * Domain path: /languages
 * Text domain: wpdtol-database-table-overview-logs
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( function_exists( 'wpdtol_database_table_overview_logs_freemius' ) ) {
    wpdtol_database_table_overview_logs_freemius()->set_basename( false, __FILE__ );
} else {
    if ( !function_exists( 'wpdtol_database_table_overview_logs_freemius' ) ) {
        function wpdtol_database_table_overview_logs_freemius() {
            global $wpdtol_database_table_overview_logs_freemius;
            if ( !isset( $wpdtol_database_table_overview_logs_freemius ) ) {
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $wpdtol_database_table_overview_logs_freemius = fs_dynamic_init( array(
                    'id'             => '12057',
                    'slug'           => 'wpdtol-database-table-overview-logs',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_911ff16c94d24b7ef4ebab8dc05e8',
                    'is_premium'     => false,
                    'premium_suffix' => 'Premium',
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'menu'           => array(
                        'slug'       => 'wpdtol-database-table-overview-logs',
                        'first-path' => 'admin.php?page=wpdtol-database-table-overview-logs',
                    ),
                    'is_live'        => true,
                ) );
            }
            return $wpdtol_database_table_overview_logs_freemius;
        }

        wpdtol_database_table_overview_logs_freemius();
        do_action( 'wpdtol_database_table_overview_logs_freemius_loaded' );
    }
    if ( !class_exists( 'WPDTOL_Database_Table_Overview_Logs' ) ) {
        define( 'WPDTOL_DATABASE_TABLE_OVERVIEW_LOGS_VERSION', '1.3.0' );
        class WPDTOL_Database_Table_Overview_Logs {
            public function __construct() {
                register_deactivation_hook( plugin_dir_path( __FILE__ ) . 'wpdtol-database-table-overview-logs.php', array($this, 'clear_scheduled_hooks') );
                // There are no scheduled hooks in free version, however this remains to ensure that if the premium version was used and no longer is then if the plugin is deactivated this hook is still cleared
                add_action( 'init', array($this, 'translation') );
                add_action( 'wp_loaded', array($this, 'upgrade') );
                add_action( 'init', array($this, 'schedule_events') );
                add_action( 'admin_enqueue_scripts', array($this, 'enqueues') );
                add_action( 'admin_menu', array($this, 'menu_pages') );
            }

            public function clear_scheduled_hooks() {
                wp_clear_scheduled_hook( 'wpdtol_database_table_overview_logs_update' );
            }

            public function translation() {
                load_plugin_textdomain( 'wpdtol-database-table-overview-logs', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
            }

            public function upgrade() {
                $version = get_option( 'wpdtol_database_table_overview_logs_version' );
                if ( WPDTOL_DATABASE_TABLE_OVERVIEW_LOGS_VERSION !== $version ) {
                    global $wpdb;
                    if ( version_compare( $version, '1.0.0', '<' ) ) {
                        $wpdb->query( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wpdtol_database_table_overview_logs ( id bigint(20) AUTO_INCREMENT, date date NOT NULL default '0000-00-00', table_name varchar(255) NOT NULL, table_size bigint(20) NOT NULL, table_rows bigint(20) NOT NULL, PRIMARY KEY (id) );" );
                    }
                    update_option( 'wpdtol_database_table_overview_logs_version', WPDTOL_DATABASE_TABLE_OVERVIEW_LOGS_VERSION );
                }
            }

            public function schedule_events() {
                if ( false == wp_get_scheduled_event( 'wpdtol_database_table_overview_logs_update' ) ) {
                } else {
                    $this->clear_scheduled_hooks();
                }
            }

            public function enqueues() {
                global $pagenow;
                if ( 'admin.php' == $pagenow ) {
                    if ( isset( $_GET['page'] ) ) {
                        if ( 'wpdtol-database-table-overview-logs' == $_GET['page'] ) {
                            wp_enqueue_script( 'jquery' );
                            add_thickbox();
                            wp_enqueue_script(
                                'wpdtol-database-table-overview-logs-datatables',
                                plugins_url( 'libraries/DataTables/datatables.min.js', __FILE__ ),
                                array('jquery'),
                                WPDTOL_DATABASE_TABLE_OVERVIEW_LOGS_VERSION,
                                true
                            );
                            wp_enqueue_style(
                                'wpdtol-database-table-overview-logs-admin',
                                plugins_url( 'assets/css/admin.min.css', __FILE__ ),
                                array(),
                                WPDTOL_DATABASE_TABLE_OVERVIEW_LOGS_VERSION,
                                'all'
                            );
                        }
                    }
                }
            }

            public function menu_pages() {
                $date = ( isset( $_GET['date'] ) ? sanitize_text_field( $_GET['date'] ) : gmdate( 'Y-m-d' ) );
                add_menu_page(
                    esc_html__( 'Database Table Overview and Logs', 'wpdtol-database-table-overview-logs' ) . ' ' . esc_html__( '-', 'wpdtol-database-table-overview-logs' ) . ' ' . $this->format_date( $date ),
                    esc_html__( 'Database Table Overview & Logs', 'wpdtol-database-table-overview-logs' ),
                    'manage_options',
                    'wpdtol-database-table-overview-logs',
                    array($this, 'dashboard'),
                    'dashicons-database',
                    '100'
                );
            }

            public function dashboard() {
                global $wpdb;
                $clear_logs_notice = '';
                if ( isset( $_POST['clear_logs'] ) && isset( $_POST['clear_logs_period'] ) ) {
                    $this->clear_logs( sanitize_text_field( $_POST['clear_logs_period'] ) );
                    $clear_logs_notice = '<div class="notice notice-success inline is-dismissible"><p>' . esc_html__( 'Logs cleared.', 'wpdtol-database-table-overview-logs' ) . '</p></div>';
                } else {
                    $this->update_logs();
                }
                $date_earliest = $this->get_logs_earliest_date();
                $date_today = get_date_from_gmt( gmdate( 'Y-m-d H:i:s' ), 'Y-m-d' );
                $date = ( isset( $_GET['date'] ) ? sanitize_text_field( $_GET['date'] ) : $date_today );
                $logs = $this->get_logs( $date );
                ?>

				<div id="wpdtol-database-table-overview-logs" class="wrap">
					<h1 class="wp-heading-inline"><?php 
                esc_html_e( 'Database Table Overview and Logs', 'wpdtol-database-table-overview-logs' );
                ?></h1>
					<?php 
                ?>
						<div class="notice notice-info inline">
							<p>
								<?php 
                // translators: %s: upgrade link
                echo wp_kses_post( sprintf( __( '%s for automatic daily logging, email reports, print and export. Logs will be generated automatically every day. An email report can also be sent daily and you can print or export the table below.', 'wpdtol-database-table-overview-logs' ), '<strong><a href="' . esc_url( get_admin_url() . 'admin.php?page=wpdtol-database-table-overview-logs-pricing' ) . '">' . esc_html__( 'Upgrade to premium', 'wpdtol-database-table-overview-logs' ) . '</a></strong>' ) );
                ?>
							</p>
						</div>
						<?php 
                echo wp_kses_post( $clear_logs_notice );
                ?>
					<div id="wpdtol-database-table-overview-logs-actions">
						<div>
							<form method="get">
								<input type="hidden" name="page" value="wpdtol-database-table-overview-logs">
								<label><?php 
                esc_html_e( 'Date:', 'wpdtol-database-table-overview-logs' );
                ?> <input type="date" name="date" value="<?php 
                echo esc_attr( $date );
                ?>" min="<?php 
                echo esc_attr( $date_earliest );
                ?>" max="<?php 
                echo esc_attr( $date_today );
                ?>" required></label>
								<input type="submit" class="button button-primary" value="<?php 
                esc_html_e( 'Set', 'wpdtol-database-table-overview-logs' );
                ?>">
							</form>
						</div>
						<?php 
                ?>
						<div>
							<form method="post" onsubmit="return confirm( '<?php 
                esc_html_e( 'Are you sure you want to clear logs?', 'wpdtol-database-table-overview-logs' );
                ?>' );">
								<input type="hidden" name="page" value="wpdtol-database-table-overview-logs">
								<label>
									<?php 
                esc_html_e( 'Clear logs: ', 'wpdtol-database-table-overview-logs' );
                ?>
									<select name="clear_logs_period">
										<option value="all"><?php 
                esc_html_e( 'All', 'wpdtol-database-table-overview-logs' );
                ?></option>
										<option value="1"><?php 
                esc_html_e( '> 1 month', 'wpdtol-database-table-overview-logs' );
                ?></option>
										<option value="3"><?php 
                esc_html_e( '> 3 months', 'wpdtol-database-table-overview-logs' );
                ?></option>
										<option value="6"><?php 
                esc_html_e( '> 6 months', 'wpdtol-database-table-overview-logs' );
                ?></option>
										<option value="12"><?php 
                esc_html_e( '> 12 months', 'wpdtol-database-table-overview-logs' );
                ?></option>
									</select>
								</label>
								<input type="submit" name="clear_logs" class="button button-secondary" value="<?php 
                esc_html_e( 'Clear', 'wpdtol-database-table-overview-logs' );
                ?>">
							</form>
						</div>
					</div>
					<div class="notice notice-info inline">
						<p>
							<?php 
                esc_html_e( 'Showing logs for:', 'wpdtol-database-table-overview-logs' );
                ?> <strong><?php 
                echo esc_html( $this->format_date( $date ) );
                ?></strong> &bull;
							<?php 
                esc_html_e( 'Logs available from:', 'wpdtol-database-table-overview-logs' );
                ?> <strong><?php 
                echo esc_html( $this->format_date( $date_earliest ) );
                ?></strong> &bull;
							<?php 
                esc_html_e( 'Logging:', 'wpdtol-database-table-overview-logs' );
                ?>
							<?php 
                ?>
								<strong><?php 
                esc_html_e( 'Manual', 'wpdtol-database-table-overview-logs' );
                ?> <?php 
                esc_html_e( '(each time this dashboard accessed)', 'wpdtol-database-table-overview-logs' );
                ?></strong>
							<?php 
                ?>
						</p>
						<p>
							<?php 
                esc_html_e( 'Database name:', 'wpdtol-database-table-overview-logs' );
                ?> <strong><?php 
                echo esc_html( $wpdb->dbname );
                ?></strong> &bull;
							<?php 
                esc_html_e( 'Database size on this date:', 'wpdtol-database-table-overview-logs' );
                ?> <strong><span id="wpdtol-database-table-overview-logs-database-size"><img src="<?php 
                echo esc_url( includes_url( 'images/spinner.gif' ) );
                ?>" class="wpdtol-database-table-overview-logs-spinner"></span></strong> &bull;
							<?php 
                esc_html_e( 'Database rows on this date:', 'wpdtol-database-table-overview-logs' );
                ?> <strong><span id="wpdtol-database-table-overview-logs-database-rows"><img src="<?php 
                echo esc_url( includes_url( 'images/spinner.gif' ) );
                ?>" class="wpdtol-database-table-overview-logs-spinner"></span></strong>
						</p>
					</div>
					<script>
						jQuery( document ).ready( function( $ ) {
							if ( $( '#wpdtol-database-table-overview-logs-database-size-calculated' ).text() !== '' ) {
								$( '#wpdtol-database-table-overview-logs-database-size' ).text( $( '#wpdtol-database-table-overview-logs-database-size-calculated' ).text() );
							} else {
								$( '#wpdtol-database-table-overview-logs-database-size' ).text( '<?php 
                esc_html_e( 'No logs yet', 'wpdtol-database-table-overview-logs' );
                ?>' );
							}
							if ( $( '#wpdtol-database-table-overview-logs-database-rows-calculated' ).text() !== '' ) {
								$( '#wpdtol-database-table-overview-logs-database-rows' ).text( $( '#wpdtol-database-table-overview-logs-database-rows-calculated' ).text() );
							} else {
								$( '#wpdtol-database-table-overview-logs-database-rows' ).text( '<?php 
                esc_html_e( 'No logs yet', 'wpdtol-database-table-overview-logs' );
                ?>' );
							}
						});
					</script>
					<?php 
                if ( !empty( $logs ) ) {
                    $this->logs_table( $logs, 'page', $date );
                    $dom = '<"top"if>t';
                    ?>
						<script>
							jQuery( document ).ready( function( $ ) {
								$( '#wpdtol-database-table-overview-logs-table' ).DataTable({
									'dom': '<?php 
                    echo $dom;
                    // phpcs:ignore
                    ?>',
									'order': [ 0, 'asc' ],
									'columnDefs': [
										{ type: 'num', targets: 1 },
										{ type: 'num', targets: 2 },
									],
									'pageLength': <?php 
                    echo esc_html( PHP_INT_MAX );
                    ?>,
									<?php 
                    ?>
									'language': {
										'decimal': 			'',
										'emptyTable':		"<?php 
                    esc_html_e( 'No data available', 'wpdtol-database-table-overview-logs' );
                    ?>",
										<?php 
                    // translators: %1$s: start, %2$s: end, %3$s: total
                    ?>
										'info':				"<?php 
                    echo sprintf(
                        esc_html__( 'Showing %1$s to %2$s of %3$s tables', 'wpdtol-database-table-overview-logs' ),
                        '_START_',
                        '_END_',
                        '_TOTAL_'
                    );
                    ?>",
										'infoEmpty':		"<?php 
                    esc_html_e( 'Showing 0 to 0 of 0 tables', 'wpdtol-database-table-overview-logs' );
                    ?>",
										<?php 
                    // translators: %s: total
                    ?>
										'infoFiltered':		"<?php 
                    echo sprintf( esc_html__( '(filtered from %s)', 'wpdtol-database-table-overview-logs' ), '_MAX_' );
                    ?>",
										'infoPostFix':		'',
										'thousands':		'',
										'loadingRecords':	"<?php 
                    esc_html_e( 'Loading...', 'wpdtol-database-table-overview-logs' );
                    ?>",
										'processing':		"<?php 
                    esc_html_e( 'Processing...', 'wpdtol-database-table-overview-logs' );
                    ?>",
										'search':			"<?php 
                    esc_html_e( 'Search:', 'wpdtol-database-table-overview-logs' );
                    ?> ",
										'zeroRecords':		"<?php 
                    esc_html_e( 'No tables found.', 'wpdtol-database-table-overview-logs' );
                    ?>",
										'aria': {
											'sortAscending':	"<?php 
                    esc_html_e( ':', 'wpdtol-database-table-overview-logs' );
                    ?> <?php 
                    esc_html_e( 'activate to sort column ascending', 'wpdtol-database-table-overview-logs' );
                    ?>",
											'sortDescending':	"<?php 
                    esc_html_e( ':', 'wpdtol-database-table-overview-logs' );
                    ?> <?php 
                    esc_html_e( 'activate to sort column descending', 'wpdtol-database-table-overview-logs' );
                    ?>"
										}
									},
								});
							});
						</script>
					<?php 
                } else {
                    ?>
						<div class="notice notice-error inline">
							<p>
								<?php 
                    // translators: %s: refresh link
                    echo wp_kses_post( sprintf( __( 'No logs yet, if you have just cleared logs then %s to update the log for today.', 'wpdtol-database-table-overview-logs' ), '<a href="' . esc_url( get_admin_url() . 'admin.php?page=wpdtol-database-table-overview-logs' ) . '">' . esc_html__( 'refresh the dashboard', 'wpdtol-database-table-overview-logs' ) . '</a>' ) );
                    ?>
							</p>
						</div>
					<?php 
                }
                ?>
				</div>

				<?php 
            }

            public function logs_table( $logs, $type, $date ) {
                global $wpdb;
                $date = $this->format_date( $date );
                ob_start();
                // Table and surrounding elements, this ends up being the echo/return
                ob_start();
                // Table only, placed below totals after calculations in each row iteration
                ?>

				<table<?php 
                echo ( 'page' == $type ? ' id="wpdtol-database-table-overview-logs-table" class="widefat fixed striped"' : '' );
                ?>>
					<thead>
						<tr>
							<th><?php 
                echo (( 'page' == $type ? '<a href="#">' : '' )) . esc_html__( 'Table name', 'wpdtol-database-table-overview-logs' ) . (( 'page' == $type ? '</a>' : '' ));
                ?></th>
							<th><?php 
                echo (( 'page' == $type ? '<a href="#">' : '' )) . esc_html__( 'Table size', 'wpdtol-database-table-overview-logs' ) . ' ' . esc_html__( '(MB)', 'wpdtol-database-table-overview-logs' ) . (( 'page' == $type ? '</a>' : '' ));
                ?></th>
							<th><?php 
                echo (( 'page' == $type ? '<a href="#">' : '' )) . esc_html__( 'Table rows', 'wpdtol-database-table-overview-logs' ) . (( 'page' == $type ? '</a>' : '' ));
                ?></th>
						</tr>
					</thead>
					<tbody>
						<?php 
                $total_table_size = 0;
                $total_table_rows = 0;
                $modals = array();
                foreach ( $logs as $log ) {
                    $total_table_size = $total_table_size + (int) $log->table_size;
                    $total_table_rows = $total_table_rows + (int) $log->table_rows;
                    if ( 'page' == $type ) {
                        ob_start();
                        // Modal content
                        // Below is not in a $wpdb->prepare hence the phpcs:ignore because %s placeholder adds quotes and table name cannot be found with them included, note that https://developer.wordpress.org/reference/classes/wpdb/prepare/ states using %1$s should not include quotes but when used the query is incorrect, outputs %1 not the table name
                        $table_data = $wpdb->get_results( "SELECT * FROM " . $log->table_name . " ORDER BY RAND() LIMIT 1;" );
                        // phpcs:ignore
                        ?>

								<p><strong><?php 
                        esc_html_e( 'Table info as of', 'wpdtol-database-table-overview-logs' );
                        ?> <?php 
                        echo esc_html( $date ) . esc_html__( ':', 'wpdtol-database-table-overview-logs' );
                        ?></strong></p>

								<table class="widefat fixed striped">
									<tbody>
										<tr>
											<td><?php 
                        esc_html_e( 'Name', 'wpdtol-database-table-overview-logs' );
                        ?></td>
											<td><?php 
                        echo esc_html( $log->table_name );
                        ?></td>
										</tr>
										<tr>
											<td><?php 
                        esc_html_e( 'Size', 'wpdtol-database-table-overview-logs' );
                        ?></td>
											<td><?php 
                        echo esc_html( $this->format_size( $log->table_size ) );
                        ?></td>
										</tr>
										<tr>
											<td><?php 
                        esc_html_e( 'Rows', 'wpdtol-database-table-overview-logs' );
                        ?></td>
											<td><?php 
                        echo esc_html( $log->table_rows );
                        ?></td>
										</tr>
									</tbody>
								</table>

								<p><strong><?php 
                        echo esc_html__( 'Random row from table as of today:', 'wpdtol-database-table-overview-logs' );
                        ?></strong></p>

								<?php 
                        if ( !empty( $table_data ) ) {
                            $table_data = $table_data[0];
                            ?>

									<table class="widefat fixed striped">
										<tbody>
											<?php 
                            foreach ( $table_data as $table_data_column => $table_data_value ) {
                                ?>
												<tr>
													<td><?php 
                                echo esc_html( $table_data_column );
                                ?></td>
													<td><?php 
                                echo esc_html( $table_data_value );
                                ?></td>
												</tr>
												<?php 
                            }
                            ?>
										</tbody>
									</table>

									<?php 
                        } else {
                            ?>

									<p><?php 
                            esc_html_e( 'No data available.', 'wpdtol-database-table-overview-logs' );
                            ?></p>

									<?php 
                        }
                        $modal_content = ob_get_clean();
                        $modals[] = '<div id="wpdtol-database-table-overview-logs-modal-' . esc_attr( str_replace( '_', '-', $log->table_name ) ) . '" class="wpdtol-database-table-overview-logs-hidden">' . $modal_content . '</div>';
                    }
                    ?>

							<tr>
								<td>
									<?php 
                    if ( 'page' == $type ) {
                        ?>
										<a href="#TB_inline?&width=700&height=500&inlineId=wpdtol-database-table-overview-logs-modal-<?php 
                        echo esc_attr( str_replace( '_', '-', $log->table_name ) );
                        ?>" class="thickbox" title="<?php 
                        echo esc_attr( $log->table_name );
                        ?>"><?php 
                        echo esc_html( $log->table_name );
                        ?></a>
									<?php 
                    } else {
                        ?>
										<?php 
                        echo esc_html( $log->table_name );
                        ?>
									<?php 
                    }
                    ?>
								</td>
								<td><?php 
                    echo esc_html( str_replace( ' ' . esc_html__( 'MB', 'wpdtol-database-table-overview-logs' ), '', $this->format_size( $log->table_size ) ) );
                    // MB has to be removed to ensure column sorting works correctly
                    ?></td>
								<td><?php 
                    echo esc_html( $log->table_rows );
                    ?></td>
							</tr>
							<?php 
                }
                ?>
					</tbody>
				</table>

				<?php 
                $table = ob_get_clean();
                if ( 'page' == $type ) {
                    ?>

					<div id="wpdtol-database-table-overview-logs-database-size-calculated" class="wpdtol-database-table-overview-logs-hidden"><?php 
                    echo esc_html( $this->format_size( $total_table_size ) );
                    ?></div>
					<div id="wpdtol-database-table-overview-logs-database-rows-calculated" class="wpdtol-database-table-overview-logs-hidden"><?php 
                    echo esc_html( $total_table_rows );
                    ?></div>

					<?php 
                }
                echo wp_kses_post( $table );
                if ( !empty( $modals ) && 'page' == $type ) {
                    foreach ( $modals as $modal ) {
                        echo wp_kses_post( $modal );
                    }
                }
                if ( 'page' == $type ) {
                    echo wp_kses_post( ob_get_clean() );
                } else {
                    return wp_kses_post( ob_get_clean() );
                }
            }

            public function format_date( $date ) {
                $date_format = ( !empty( get_option( 'date_format' ) ) ? get_option( 'date_format' ) : 'Y-m-d' );
                return gmdate( $date_format, strtotime( esc_html( $date ) ) );
            }

            public function format_size( $size ) {
                return round( (float) $size / 1024 / 1024, 2 ) . ' ' . esc_html__( 'MB', 'wpdtol-database-table-overview-logs' );
            }

            public function get_logs( $date ) {
                global $wpdb;
                $logs = array();
                if ( !empty( $date ) ) {
                    $logs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}wpdtol_database_table_overview_logs` WHERE `date` = %s;", $date ) );
                }
                return $logs;
            }

            public function get_logs_earliest_date() {
                global $wpdb;
                $earliest_date = $wpdb->get_results( "SELECT MIN( date ) AS `date` FROM `{$wpdb->prefix}wpdtol_database_table_overview_logs`;" );
                return ( !empty( $earliest_date ) ? $earliest_date[0]->date : gmdate( 'Y-m-d' ) );
            }

            public function update_logs() {
                global $wpdb;
                $tables_data = $wpdb->get_results( "SELECT `TABLE_NAME`, `TABLE_ROWS`, `DATA_LENGTH`, `INDEX_LENGTH` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` = '" . $wpdb->dbname . "';" );
                if ( !empty( $tables_data ) ) {
                    $date = gmdate( 'Y-m-d' );
                    $wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}wpdtol_database_table_overview_logs` WHERE `date` = %s;", $date ) );
                    foreach ( $tables_data as $table_data ) {
                        $table_name = $table_data->TABLE_NAME;
                        $table_size = (int) $table_data->DATA_LENGTH + (int) $table_data->INDEX_LENGTH;
                        $table_rows = $table_data->TABLE_ROWS;
                        $wpdb->query( $wpdb->prepare(
                            "INSERT INTO `{$wpdb->prefix}wpdtol_database_table_overview_logs` ( `date`, `table_name`, `table_size`, `table_rows` ) VALUES ( %s, %s, %d, %d );",
                            $date,
                            $table_name,
                            $table_size,
                            $table_rows
                        ) );
                    }
                }
            }

            public function clear_logs( $period ) {
                if ( !empty( $period ) ) {
                    global $wpdb;
                    $date_today = gmdate( 'Y-m-d' );
                    if ( 'all' == $period ) {
                        $wpdb->query( "TRUNCATE `{$wpdb->prefix}wpdtol_database_table_overview_logs`;" );
                    } else {
                        $date_less_than = gmdate( 'Y-m-d', strtotime( '-' . $period . ' months', strtotime( $date_today ) ) );
                        $wpdb->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}wpdtol_database_table_overview_logs` WHERE `date` < %s;", $date_less_than ) );
                    }
                }
            }

        }

        new WPDTOL_Database_Table_Overview_Logs();
    }
}