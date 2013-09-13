<?php
/*
Plugin Name: Pousadinhas Search Widget
Plugin URI: http://www.pousadinhas.com.br/recursos#widget
Description: A plugin to display the Pousadinhas.com.br online booking search widget. Please find 'Pousadinhas Search Widget' under the Available Widgets area.
Version: 1.0.0
Author: Pousadinhas
Author URI: http://www.pousadinhas.com.br/
Text Domain: pousadinhas-search-widget
Domain Path: languages
*/

class Pousadinhas_Search_Widget extends WP_Widget
{
	const DOMAIN='www.pousadinhas.com.br';

	public function __construct()
	{
		parent::__construct('Pousadinhas_Search_Widget',__('Pousadinhas Search Widget','pousadinhas-search-widget'),array(
			'classname'=>'Pousadinhas_Search_Widget',
			'description'=>__('Displays the Pousadinhas.com.br online booking search widget.','pousadinhas-search-widget'),
		));

		$domain=self::DOMAIN;
		add_action('wp_enqueue_scripts',function() use ($domain)
		{
			wp_enqueue_script('pousadinhas-search-widget','//'.$domain.'/js/widget.js');
		});

		/*
		$field_id=$this->get_field_id('destination');
		add_action( 'admin_enqueue_scripts',function()
		{
			wp_enqueue_script('jquery-ui-autocomplete');
		});
		add_action('admin_footer',function() use ($field_id)
		{
			echo '
			<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery("#'.$field_id.'").autocomplete({
					"minLength":0,
					"delay":500,
					"focus":function(event, ui) {
						var l = ui.item.label;
						$("#'.$field_id.'").val(l.substr(0,l.indexOf(",")));
						return false;
					},
					"select":function(event, ui) {
						var label = ui.item.label;
						var keyname = ui.item.keyname;
						var place = "";
						var inn = "";
						switch (ui.item.category) {
						case "L": place = ui.item.value; break;
						case "P": inn = ui.item.value; break;
						}
						$("#'.$field_id.'").val(label.substr(0,label.indexOf(",")));

						//$("#SearchForm_lugar").val(place);
						//$("#SearchForm_pousada").val(inn);

						return false;
					},
					"showAnim":"",
					//"source":"/search/suggest",
					"shource":[
						{
							label: "Tiradentes, MG",
							category: "L",
							value: "1",
						},
					],
				});
			});
			</script>
			';
		});
		*/
	}

	public function widget($args,$instance)
	{
		$destination=$instance['destination'];
		$suggest_dates=$instance['suggest_dates'];
		$coupling=$instance['coupling'];
		$compact=$instance['compact'];

		$language=get_bloginfo('language');
		$language=strtolower(substr($language,0,2));
		if(!in_array($language,array('de','en','es','fr','it','ja'))) $language='';

		$src='//'.self::DOMAIN.'/'.$destination.'/widget';

		$params=array();
		if(!$suggest_dates)
		{
			$params['entrada']='';
			$params['saida']='';
		}
		if(!empty($language)) $params['idioma']=$language;
		if(!empty($coupling)) $params['coupling']=$coupling;
		$query=http_build_query($params);
		if(!empty($query)) $src.='?'.$query;

		if($compact)
		{
			$width=210;
			$height=300;
		}
		else
		{
			$width=280;
			$height=350;
		}

		if(empty($coupling)) echo $args['before_widget'];
		echo '<iframe width="'.$width.'" height="'.$height.'" frameborder="0" scrolling="no" allowTransparency="true" src="'.$src.'"></iframe>';
		if(empty($coupling)) echo $args['after_widget'];
	}

	public function form($instance)
	{
		$instance=wp_parse_args($instance,array(
			'destination'=>'',
			'suggest_dates'=>true,
			'coupling'=>'',
			'compact'=>false,
		));
		?>

		<p>
		<label for="<?php echo $this->get_field_id('destination'); ?>"><?php _e('Destination:','pousadinhas-search-widget'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('destination'); ?>" name="<?php echo $this->get_field_name('destination'); ?>" type="text" value="<?php echo esc_attr($instance['destination']); ?>" />
		</p>

		<p>
		<input id="<?php echo $this->get_field_id('suggest_dates'); ?>" name="<?php echo $this->get_field_name('suggest_dates'); ?>" type="checkbox" <?php checked($instance['suggest_dates']); ?> />
		<label for="<?php echo $this->get_field_id('suggest_dates'); ?>"><?php _e('Suggest dates','pousadinhas-search-widget'); ?></label>
		</p>

		<p>
		<label for="<?php echo $this->get_field_id('coupling'); ?>"><?php _e('Coupling:','pousadinhas-search-widget'); ?></label>
		<select id="<?php echo $this->get_field_id('coupling'); ?>" name="<?php echo $this->get_field_name('coupling'); ?>">
		<option value="" <?php selected('',$instance['coupling']); ?>><?php _e('None','pousadinhas-search-widget'); ?></option>
		<option value="NE" <?php selected('NE',$instance['coupling']); ?>><?php _e('Northeast','pousadinhas-search-widget'); ?></option>
		<option value="NO" <?php selected('NO',$instance['coupling']); ?>><?php _e('Northwest','pousadinhas-search-widget'); ?></option>
		<option value="SE" <?php selected('SE',$instance['coupling']); ?>><?php _e('Southeast','pousadinhas-search-widget'); ?></option>
		<option value="SO" <?php selected('SO',$instance['coupling']); ?>><?php _e('Southwest','pousadinhas-search-widget'); ?></option>
		</select>
		</p>

		<p>
		<input id="<?php echo $this->get_field_id('compact'); ?>" name="<?php echo $this->get_field_name('compact'); ?>" type="checkbox" <?php checked($instance['compact']); ?> />
		<label for="<?php echo $this->get_field_id('compact'); ?>"><?php _e('Compact','pousadinhas-search-widget'); ?></label>
		</p>

		<?php
	}

	public function update($new_instance,$old_instance)
	{
		$instance=$old_instance;
		$instance['destination']=trim($new_instance['destination']);
		$instance['suggest_dates']=(bool)$new_instance['suggest_dates'];
		if(in_array($new_instance['coupling'],array('','NE','NO','SE','SO')))
		{
			$instance['coupling']=$new_instance['coupling'];
		}
		$instance['compact']=(bool)$new_instance['compact'];
		return $instance;
	}
}

add_action('widgets_init',function()
{
	return register_widget('Pousadinhas_Search_Widget');
});

load_plugin_textdomain('pousadinhas-search-widget',false,dirname(plugin_basename( __FILE__ )).'/languages/');
