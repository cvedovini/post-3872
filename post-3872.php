<?php
/*
Plugin Name: post-3872
Plugin URI: http://vdvn.me/p2zk
Description: This plugin demonstrate how to use the Settings API with network
  admin options pages in a WordPress multisite installation
Author: Claude Vedovini
Author URI: http://vdvn.me/
Version: 1
Text Domain: post3872

# The code in this plugin is free software; you can redistribute the code aspects of
# the plugin and/or modify the code under the terms of the GNU Lesser General
# Public License as published by the Free Software Foundation; either
# version 3 of the License, or (at your option) any later version.

# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
# EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
# MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
# NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
# LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
# OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
# WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
#
# See the GNU lesser General Public License for more details.
*/

/**
 * First let's hook up to the 'network_admin_menu' action and create an options
 * page.
 *
 * Then we use the Settings API to create a section and register an option.
 *
 * You will notice the only difference with coding a normal page is the parent
 * slug of our option page (must be 'settings.php') and the capability required
 * for this page to be displayed (here 'manage_network_options').
 */
add_filter('network_admin_menu', 'post3872_network_admin_menu');
function post3872_network_admin_menu() {
    // Create our options page.
    add_submenu_page('settings.php', __('My Network Options', 'post3872'),
        __('My Plugin', 'post3872'), 'manage_network_options',
        'post3872_network_options_page', 'post3872_network_options_page_callback');

    // Create a section (we won't need a section header).
    add_settings_section('default', __('Default Network Options'), false,
        'post3872_network_options_page');

    // Create and register our option (we make the option id very explicit because
    // this is the key that will be used to store the options.
    register_setting('post3872_network_options_page', 'post3872_network_option_1');
    add_settings_field('post3872_network_option_1', __('Network option one', 'post3872'),
        'post3872_network_option_1_callback', 'post3872_network_options_page',
        'default');
}

/**
 * Displays our only option. Nothing special here.
 */
function post3872_network_option_1_callback() { ?>
<label><input type="checkbox" name="post3872_network_option_1"
  value="1" <?php checked(get_site_option('post3872_network_option_1')); ?> />&nbsp;<?php
  _e('Check this box if you want to activate network option one.', 'post3872') ?></label><?php
}

/**
 * Displays the options page. The big difference here is where you post the data
 * because, unlike for normal option pages, there is nowhere to process it by
 * default so we have to create our own hook to process the saving of our options.
 */
function post3872_network_options_page_callback() {
  if (isset($_GET['updated'])): ?>
<div id="message" class="updated notice is-dismissible"><p><?php _e('Options saved.') ?></p></div>
  <?php endif; ?>
<div class="wrap">
  <h1><?php _e('My Network Options', 'post3872'); ?></h1>
  <form method="POST" action="edit.php?action=post3872_update_network_options"><?php
    settings_fields('post3872_network_options_page');
    do_settings_sections('post3872_network_options_page');
    submit_button(); ?>
  </form>
</div>
<?php
}


/**
 * This function here is hooked up to a special action and necessary to process
 * the saving of the options. This is the big difference with a normal options
 * page.
 */
add_action('network_admin_edit_post3872_update_network_options',  'post3872_update_network_options');
function post3872_update_network_options() {
  // Make sure we are posting from our options page. There's a little surprise
  // here, on the options page we used the 'post3872_network_options_page'
  // slug when calling 'settings_fields' but we must add the '-options' postfix
  // when we check the referer.
  check_admin_referer('post3872_network_options_page-options');

  // This is the list of registered options.
  global $new_whitelist_options;
  $options = $new_whitelist_options['post3872_network_options_page'];

  // Go through the posted data and save only our options. This is a generic
  // way to do this, but you may want to address the saving of each option
  // individually.
  foreach ($options as $option) {
    if (isset($_POST[$option])) {
      // Save our option with the site's options.
      // If we registered a callback function to sanitizes the option's
      // value it will be called here (see register_setting).
      update_site_option($option, $_POST[$option]);
    } else {
      // If the option is not here then delete it. It depends on how you
      // want to manage your defaults however.
      delete_site_option($option);
    }
  }

  // At last we redirect back to our options page.
  wp_redirect(add_query_arg(array('page' => 'post3872_network_options_page',
      'updated' => 'true'), network_admin_url('settings.php')));
  exit;
}


/*****************************************************************************/
/* For the sake of demonstrating the differences between a normal options    */
/* page and a network option page, let's create a normal page too.           */
/*****************************************************************************/
add_filter('admin_menu', 'post3872_admin_menu');
function post3872_admin_menu() {
  add_submenu_page('options-general.php', __('My Options', 'post3872'),
      __('My Plugin', 'post3872'), 'manage_options',
      'post3872_options_page', 'post3872_options_page_callback');

  add_settings_section('default', __('Default Options'), false,
      'post3872_options_page');

  register_setting('post3872_options_page', 'post3872_option_2');
  add_settings_field('post3872_option_2', __('Option two', 'post3872'),
      'post3872_option_2_callback', 'post3872_options_page', 'default');
}

function post3872_option_2_callback() { ?>
<label><input type="checkbox" name="post3872_option_2"
  value="1" <?php checked(get_option('post3872_option_2')); ?> />&nbsp;<?php
  _e('Check this box if you want to activate option two.', 'post3872') ?></label><?php
}

function post3872_options_page_callback() { ?>
<div class="wrap">
  <h1><?php _e('My Options', 'post3872'); ?></h1>
  <form method="POST" action="options.php"><?php
    settings_fields('post3872_options_page');
    do_settings_sections('post3872_options_page');
    submit_button(); ?>
  </form>
</div>
<?php
}
