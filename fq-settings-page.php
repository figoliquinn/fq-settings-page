<?php
/**
 * Plugin Name: FQ Settings
 * Plugin URI: http://figoliquinn.github.io/fq-settings-page/
 * Description: A light-weight settings page tool.
 * Version: 1.0.0
 * Author: Bob Passaro & Tony Figoli
 * Author URI: http://figoliquinn.com
 * License: GPL2
*/









if ( !class_exists('FQ_Settings') ) {


	class FQ_Settings {


		public $parent_slug = 'options-general.php'; // set to false for top menu

		public $page_title = 'My Settings';

		public $page_intro = 'lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum ';

		public $menu_title = 'My Settings';

		public $capability = 'administrator';

		public $menu_slug = 'my-settings';
	
		public $settings = array();

		public $icon_url;
	
		public $position;



		function __construct() {


			add_action('admin_menu', array($this,'create') );
			add_action('admin_enqueue_scripts', array($this,'media_admin_scripts') );

		}

		function create() {
		
			if(!$this->settings) return;
			
			// add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
			// add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );

			if(!$this->parent_slug) {
			
				add_menu_page( $this->page_title, $this->menu_title, $this->capability, $this->menu_slug, array($this,'display') , $this->icon_url, $this->position );
		
			} else {
			
				add_submenu_page( $this->parent_slug , $this->page_title , $this->menu_title , $this->capability , $this->menu_slug , array($this,'display') );
			}

			//call register settings function
			add_action( 'admin_init', array($this,'register') );
	
		}

		function media_admin_scripts() {

			wp_enqueue_media();

		}

		function register() {
		
			foreach($this->settings as $n => $setting ) {
	
				add_option( $setting['name'], $setting['value']);		
				register_setting( $this->menu_slug , $setting['name'] );
			}

		}
	
		function display() {

			$action = 'update-'.$this->menu_slug;
		
			#print_r($_POST);
		
			if( wp_verify_nonce($_POST['_wpnonce'], $action) ) {

				foreach( $this->settings as $n => $setting ) {

					update_option( $setting['name'] , $_POST[$setting['name']] );
				}
			}
		
	?>

			<div class="wrap">

				<h2><?php echo $this->page_title; ?></h2>
				<div><?php echo $this->page_intro; ?></div>

				<form method="post" action="" enctype="multipart/form-data">

					<div class="metabox-holder">
						<div class="meta-box-sortables ui-sortable">
							<div class="postbox">

								<h3 class="hndle"><span><?php echo $this->menu_title; ?></span></h3>
								<div class="inside">


									<table class="form-table">
									<?php
									foreach( $this->settings as $n => $setting ) {
										switch($setting['type']){
											case 'select':
												$this->select_element($setting);
											break;
											case 'radio':
												$this->radio_element($setting);
											break;
											case 'checkbox':
												$this->checkbox_element($setting);
											break;
											case 'textarea':
												$this->textarea_element($setting);
											break;
											case 'upload':
												$this->upload_element($setting);
											break;
											default:
												$this->text_element($setting);
											break;
										}
									}
									?>
									</table>
								</div>

							</div>
						</div>
					</div>

				<?php submit_button( "Save ".$this->menu_title ); ?>

				<?php wp_nonce_field( $action ) ?>

				</form>

			</div><!-- end .wrap -->
		
	<?php	
		}

		function element_description($args=array()) { if($args['description']) : ?>
	
			<p class="description"><?php echo $args['description']; ?></p>
	
		<?php endif; }

		function text_element($args=array()) { $args['value'] = get_option($args['name']); ?>
	
				<tr valign="top">
					<th scope="row"><?php echo $args['label']; ?></th>
					<td>
						<input type="text" name="<?php echo $args['name']; ?>" value="<?php echo esc_attr($args['value']); ?>" 
						class="<?php echo $args['class'] ? $args['class'] : 'regular-text'; ?>" />
						<?php $this->element_description($args); ?>
					</td>
				</tr>
	
		<?php }

		function checkbox_element($args=array(
			'label'=>'',
			'name'=>'',
			'options'=>array(),
			'value'=>'',
		),$radio=false) { $args['value']=get_option($args['name']); ?>
	
				<tr valign="top">
				<th scope="row"><?php echo $args['label']; ?></th>
				<td>
					<?php $count=0; foreach($args['options'] as $value => $label) : $count++; ?>
					<?php if(!$radio) { $checked = ($args['value']&&in_array($value,(array)$args['value'])) ? ' checked="checked" ' : ''; } else { $checked = $value==$args['value'] ? ' checked="checked" ' : ''; } ?>
					<label>
						<input <?php echo $checked; ?> type="<?php echo $radio ? 'radio' : 'checkbox'; ?>" name="<?php echo $args['name']; ?><?php echo $radio ? '' : '[]'; ?>" value="<?php echo $value; ?>" />
						<?php echo $label; ?>
					</label><br />
					<?php endforeach; ?>
					<?php $this->element_description($args); ?>
				</td>
				</tr>
	
		<?php }

		function radio_element($args) { return $this->checkbox_element($args,true); }

		function select_element($args=array(
			'label'=>'',
			'name'=>'',
			'options'=>array(),
			'value'=>'',
			'description'=>'',
		)) { $args['value'] = get_option($args['name']); ?>
	
				<tr valign="top">
				<th scope="row"><?php echo $args['label']; ?></th>
				<td>
					<select name="<?php echo $args['name']; ?>" class="">
					<?php $count=0; foreach($args['options'] as $value => $label) : $count++; ?>
					<?php $selected = $args['value']==$value ? ' selected="selected" ' : ''; ?>
						<option value="<?php echo $value; ?>" <?php echo $selected; ?> ><?php echo $label; ?></option>
					<?php endforeach; ?>
					</select>
					<?php $this->element_description($args); ?>
				</td>
				</tr>
	
		<?php }

		function textarea_element($args=array(
			'label'=>'',
			'name'=>'',
			'options'=>array(),
			'value'=>'',
			'rows'=>10,
		),$radio=false) { $args['value']=get_option($args['name']); ?>
	
				<tr valign="top">
				<th scope="row"><?php echo $args['label']; ?></th>
				<td>
					<textarea name="<?php echo $args['name']; ?>" rows="<?php echo $args['rows']; ?>" class="large-text" ><?php echo stripslashes(esc_attr($args['value'])); ?></textarea>
					<?php $this->element_description($args); ?>
				</td>
				</tr>
	
		<?php }

		function upload_element($args=array(
			'label'=>'',
			'name'=>'',
			'options'=>array(),
			'value'=>'',
		),$radio=false) { $args['value']=get_option($args['name']); ?>
	
				<tr valign="top">
					<th scope="row"><?php echo $args['label']; ?></th>
					<td>
						<?php if( in_array(substr($args['value'],-3,3),array('jpg','png','gif')) ) : ?>
						<img src="<?php echo $args['value']; ?>" style="height:100px;" alt="" id="<?php echo $args['name']; ?>_preview" /><br />
						<?php endif; ?>
						<input id="<?php echo $args['name']; ?>" name="<?php echo $args['name']; ?>"
							type="text" value="<?php echo esc_attr($args['value']); ?>" class="large-text" />
						<input id="<?php echo $args['name']; ?>_button" name="<?php echo $args['name']; ?>_button" 
							type="button" value="Select a File" class="button" />
						<a <?php echo ($args['value']?'':'style="display:none;"'); ?> class="button" href="<?php echo $args['value']; ?>" 
							id="<?php echo $args['name']; ?>_link" target="_blank">View Current File</a>
						<a <?php echo ($args['value']?'':'style="display:none;"'); ?> class="button" href="#" id="<?php echo $args['name']; ?>_remove">Remove Current File</a>
						<?php // settings_element_description($args); ?>
					</td>
				</tr>
				<script type="text/javascript">
					jQuery(document).ready(function($){
						var _custom_media = true, _orig_send_attachment = wp.media.editor.send.attachment;
						$('#<?php echo $args['name']; ?>_button').click(function(e) {
							var send_attachment_bkp = wp.media.editor.send.attachment;
							var button = $(this);
							var id = button.attr('id').replace('_button', '');
							var link_id = button.attr('id').replace('_button', '_link');
							var remove_id = button.attr('id').replace('_button', '_remove');
							var preview_id = button.attr('id').replace('_button', '_preview');
							_custom_media = true;
							wp.media.editor.send.attachment = function(props, attachment){
								if ( _custom_media ) {
									var url = attachment.url;
									var x;
									if(props.size){
										for (x in attachment.sizes) {
											if(x==props.size) {
												url = attachment.sizes[x].url;
											}
										}
									}
									var x = ["jpg", "png", "gif"];
									if(x.indexOf(url.substr(url.length-3,3))>-1){
										if($("#"+preview_id).length){
											$("#"+preview_id).attr('src',url);
										} else {
											$("#"+id).before( '<img src="'+url+'" id="'+preview_id+'" style="height:100px;" /><br />' );
										}
									}
									$("#"+id).val( url );
									$("#"+link_id).attr('href',attachment.url).text('View Current File').css('display','inline-block');
									$("#"+remove_id).css('display','inline-block');
								} else {
									return _orig_send_attachment.apply( this, [props, attachment] );
								};
							}
							wp.media.editor.open(button);
							return false;
						});
						$('#<?php echo $args['name']; ?>_remove').click(function(e) {
							var button = $(this);
							var id = button.attr('id').replace('_remove', '');
							var link_id = button.attr('id').replace('_remove', '_link');
							var preview_id = button.attr('id').replace('_remove', '_preview');
							$("#"+id).val('');
							$("#"+link_id).css('display','none');
							$("#"+preview_id).css('display','none');
							button.css('display','none');
							return false;
						});
					});
				</script>
	
		<?php }
		
		function delete_all($confirm=false) {
		
			if($confirm) {
				foreach($this->settings as $n => $setting ) {
					delete_option( $setting['name'] );
				}
			}
		}

	} // end class


} // end if class exists


