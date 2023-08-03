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
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;


HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');
HTMLHelper::_('bootstrap.tooltip');
$wa->registerAndUseStyle('admin-galleries', 'com_galleries/admin-galleries.css');
$wa->useScript('webcomponent.core-loader');
/*$wa->addInlineScript('
    Joomla.submitbutton = function(task) {
        spinner = document.createElement("joomla-core-loader");
        document.body.appendChild(spinner);
        Joomla.submitform(task);
    }
');*/
?>

<form
	action="<?php echo Route::_('index.php?option=com_galleries&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="gallery-form" class="form-validate form-horizontal">

	
	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'gallery')); ?>
	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'gallery', Text::_('COM_GALLERIES_TAB_GALLERY', true)); ?>
	<div class="row-fluid">
		<div class="span10 form-horizontal">
			<fieldset class="adminform">
				<legend><?php echo Text::_('COM_GALLERIES_FIELDSET_GALLERY'); ?></legend>
				<?php echo $this->form->renderField('name'); ?>
				<?php echo $this->form->renderField('user_id'); ?>
				<?php echo $this->form->renderField('gallery_images'); ?>
				<div class="container">
			        <p id="num-of-files">0</p>
			        <div id="images"></div>
			    </div>
			</fieldset>
		</div>
	</div>
	<?php echo HTMLHelper::_('uitab.endTab'); ?>
	<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
	<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
	<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
	<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
	<input type="hidden" id="deletedImages" name="deletedImages" value="0" />
	<input type="hidden" id="numExistingImages" name="numExistingImages" value="<?php echo (isset($this->item->galleries_images)?count($this->item->galleries_images):0); ?>" />
	<?php echo $this->form->renderField('created_by'); ?>
	<?php echo $this->form->renderField('modified_by'); ?>
	
	
	<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>

</form>

<script type="text/javascript">
	let fileInput = document.getElementById("jform_gallery_images");
	let imageContainer = document.getElementById("images");
	let numOfFiles = document.getElementById("num-of-files");
	const existingImages = [];

	<?php
		if (isset($this->item->galleries_images))
		{	
			foreach ($this->item->galleries_images as $gimage) {
				?> existingImages[<?php echo $gimage->id; ?>] = {id:'<?php echo $gimage->id; ?>', name:'<?php echo $gimage->name; ?>', image_url:'<?php echo $gimage->image_url; ?>' }; 
				<?php
			}
		}
	?>

	function preview() {
	    imageContainer.innerHTML = "";
	    calcNumberOfImages();
	    let idImagesRemoved = document.getElementById('deletedImages').value.split(",");

	    existingImages.forEach(function (j) {
	    	//check first if the image has been removed
	    	if (!idImagesRemoved.includes('remove-'+j.id)) {
			    let figure = document.createElement("figure");
		        let figCap = document.createElement("figcaption");
		        figCap.innerText = j.name;
		        figure.appendChild(figCap);
		        let cancelButton = document.createElement("button");
				cancelButton.data = "";
				cancelButton.innerHTML = 'X';
				cancelButton.className = 'cancel-button';
				cancelButton.setAttribute('id','remove-'+j.id);
				//cancelButton.setAttribute("onclick","alert('blah');");
				cancelButton.onclick = function(e)
				{
				    removeImage(e.target.id);
				    return false;
				}
				figure.insertBefore(cancelButton,figCap);
		        let img = document.createElement("img");
		        img.setAttribute("src",j.image_url);
		        figure.insertBefore(img,figCap);
		        imageContainer.appendChild(figure);
		    }
		});

	    for(i of fileInput.files) {
	        let reader = new FileReader();
	        let figure = document.createElement("figure");
	        let figCap = document.createElement("figcaption");
	        figCap.innerText = i.name;
	        figure.appendChild(figCap);
	        reader.onload=()=>{
	            let img = document.createElement("img");
	            img.setAttribute("src",reader.result);
	            figure.insertBefore(img,figCap);
	        }
	        imageContainer.appendChild(figure);
	        reader.readAsDataURL(i);
	    }
	}

	function calcNumberOfImages() {
		const numExistingImages = parseInt(document.getElementById('numExistingImages').value);
	    const numberFiles = fileInput.files.length + numExistingImages;
	    numOfFiles.textContent = `${numberFiles} <?php echo Text::_('COM_GALLERIES_NUM_OF_IMAGES', true); ?>`;
	}
	
	function removeImage(elemId) {
		document.body.appendChild(document.createElement("joomla-core-loader"));
        Joomla.request({
            type : "GET",
            url : "index.php?option=com_galleries&task=gallery.remove&format=json&gid="+elemId.replace('remove-',''),
            dataType : "json",
            onSuccess: function (response, xhr) {
                response = JSON.parse(response);
                let spinner = document.querySelector('joomla-core-loader');
				spinner.parentNode.removeChild(spinner);
                if (response.error) {
                    console.log("Hubo un error al eliminar la imagen");
                } else {
                    if (response.data) {
                    	document.getElementById('deletedImages').value = document.getElementById('deletedImages').value + ',' + elemId;
                    	document.getElementById('numExistingImages').value = parseInt(document.getElementById('numExistingImages').value) - 1;
						calcNumberOfImages();
	    				var element = document.getElementById(elemId).parentNode;
     					return element.parentNode.removeChild(element);
                    }
                }
            },
            onError : function (xhr) {
                let spinner = document.querySelector('joomla-core-loader');
				spinner.parentNode.removeChild(spinner);
                console.log("ajax error");
                var element = document.getElementById(elemId);
     			return element.parentNode.removeChild(element);
            }
        });
        return false;
	}

	preview();
	
</script>
