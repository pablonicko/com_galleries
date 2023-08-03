<?php
/**
 * @version    1.0.0
 * @package    com_galleries
 * @author     Pablo Tortul <pablonicko@gmail.com>
 * @copyright  2022 Pablo Tortul
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Router\Route;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('jquery.framework');
$document = Factory::getDocument();
$document->addScript('https://code.jquery.com/ui/1.8.5/jquery-ui.min.js');
$document->addScript('media/com_galleries/js/ga-scripts.js');


// Import CSS
$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_galleries.ga-styles');


if (count($this->items) == 0) {

} else {
    $item = $this->items[0];
?>
<div id="step1">
    <h2><?php echo $item->name; ?></h2>
    <h4><?php echo Text::_('COM_GALLERIES_SELECT_IMAGES'); ?></h4>
    <div class="container mt-4">
        <div class="row">
                
            <?php foreach ($item->images_galleries as $i => $ig) : ?>
                <div class="mb-3 p-3 col-6 col-sm-6 col-md-4 col-lg-3 col-xl-2">
                    <label class="image-checkbox">
                        <img su-media-id="<?php echo $ig->id; ?>" su-media-url="<?php echo $ig->image_url; ?>"
                            src="<?php echo $ig->image_url; ?>" />
                        <i class="fa fa-check"></i>
                    </label>
                </div>
            <?php endforeach; ?>
                
        </div>
        <div id="selectedmediapreview"></div>
    </div>
    <div class="button-actions">
        <button id="nextstep" class="btn btn-primary"><?php echo Text::_('COM_GALLERIES_NEXT_STEP'); ?></button>
    </div>
</div>
<div id="step2" style="display: none;">
    <h2><?php echo $item->name; ?></h2>
    <h4><?php echo Text::_('COM_GALLERIES_CONFIRM_SELECTION'); ?></h4>
    <form action="<?php echo Route::_('index.php?option=com_galleries&task=galleries.saveorder&gid=' . (int) $item->id); ?>"
    method="post" name="adminForm" id="order-form" class="form-validate form-horizontal">
    <div id="main-order"></div>
    <div class="button-actions">
        <button id="backstep" class="btn btn-info"><?php echo Text::_('COM_GALLERIES_BACK_STEP'); ?></button>
        <input type="submit" id="backstep" class="btn btn-primary" value="<?php echo Text::_('COM_GALLERIES_SEND_ORDER'); ?>" />
    </div>
</div>

<?        
    //var_dump($item);



}