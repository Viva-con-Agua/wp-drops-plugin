<h2 class="nav-tab-wrapper">
<?php

foreach ($this->page['tabs'] AS $tab) {
    $tabUrl = $this->page['url'] . '&tab=' . $tab['value'];
    $isActive = ($tab['value'] == $this->page['activeTab']);
?>

    <a href="<?= $tabUrl ?>" class="nav-tab <?= ($isActive ? 'nav-tab-active' : '' ) ?>">
        <div class="nav-tab-icon nt-<?= $tab['icon'] ?>"></div>
        <?= $tab['title'] ?>
    </a>

<?php } ?>
</h2>
