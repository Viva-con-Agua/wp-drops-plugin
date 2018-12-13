<div id="poststuff">
    <div id="post-body" class="metabox-holder columns-1">
        <div id="postbox-container-1" class="postbox-container">
            <div class="postbox ">
                <h3 class="no-hover"><span><?= __('Drops - Settings'); ?></span></h3>
                <div class="inside"><p>
                    <form method="post" action="">
                    <table class="form-table">

<?php foreach ($this->page['settings'] AS $key => $setting) { ?>

    <tr>
        <td><strong><?= $setting['title']; ?></strong></td>
        <td>
            <input type="<?= isset($setting['type']) ? $setting['type'] : 'text'; ?>" id="<?= $key; ?>" name="<?= $key; ?>" size="100" class="input" value="<?= $setting['value']; ?>"/>
            <?php if (!empty($setting['description'])) { ?>
                <p class="description"><?= $setting['description']; ?></p>
            <?php } ?>
        </td>

<?php } ?>

                    </table><br/>
                    <?php submit_button(); ?>
                    </form></p></div>
            </div>
        </div>
    </div>
</div>
