<?php

class ExmaplePlugin extends Plugin
{
	public function action_plugin_activation( $plugin_file )
	{
		Post::add_new_type( 'example' );
	}

	public function action_plugin_deactivation( $plugin_file )
	{
		Post::deactivate_post_type( 'example' );
	}

	public function filter_post_type_display($type, $foruse) 
	{ 
		$names = array( 
			'example' => array(
				'singular' => _t( 'Example', 'example' ),
				'plural' => _t( 'Examples', 'example' ),
			)
		); 
		return isset($names[$type][$foruse]) ? $names[$type][$foruse] : $type; 
	}

    public function action_form_publish_example( $form, $post )
    {
		$form->settings->append( 'checkbox', 'phpexample', 'null:null', _t( 'PHP Exec', 'phpexec' ), 'tabcontrol_checkbox' );

		if ( 0 != $post->id ) {
			$form->phpexample->value = $post->info->phpexample == 'true';
		}
    }
	
	public function action_publish_post( $post, $form )
	{
		$post->info->phpexample = $form->phpexample->value ? 'true' : 'false';
	}

	function filter_post_content_example($content, $post)
	{
		if ( $post->info->phpexample == 'true' ) {
			$content = str_replace('<!--php', '<?php', $content);
			$content = str_replace('?-->', '?>', $content);
			
			ob_start();
			$theme = Themes::create();
			eval('?>' . $content . '<?php ');
			$content = ob_get_clean();
		}
		return $content;
	}

	public function action_init()
	{
		$this->add_template('example.single', dirname($this->get_file()) . '/example.php');
	}

	public function filter_rewrite_rules( $rules )
	{
		$rules[] = new RewriteRule( array(
			'name' => 'display_examples',
			'parse_regex' => '%^examples(?:/page/(?P<page>\d+))?/?$%i',
			'build_str' => 'examples(/page/{$page})',
			'handler' => 'PluginHandler',
			'action' => 'display_examples',
			'priority' => 7,
			'is_active' => 1,
			'description' => 'Displays multiple examples',
		));

		return $rules;
	}

	public function action_plugin_act_display_examples( $handler )
	{
		$paramarray['fallback'] = array(
			'example.multiple',
			'entry.multiple',
			'multiple',
			'home',
		);

		$default_filters = array(
			'content_type' => Post::type( 'example' ),
		);
		$paramarray['user_filters'] = $default_filters;

		return $handler->theme->act_display( $paramarray );
	}
}

?>
