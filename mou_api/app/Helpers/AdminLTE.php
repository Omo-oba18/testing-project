<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Gate;

class AdminLTE
{
    public static function buildMenu()
    {
        $menus = config('menu');
        if (! isset($menus) || count($menus) == 0) {
            return;
        }

        foreach ($menus as $parent) {
            if (! empty($parent['permission']) && ! Gate::allows($parent['permission'])) {
                continue;
            }
            // active parent
            $active = false;
            // show parent
            $parentShow = false;
            if (! empty($parent['child'])) {
                foreach ($parent['child'] as $c) {
                    if (request()->is($c['href']) || request()->is($c['href'].'/*')) {
                        $active = true;
                    }
                    // Show parent if more than one child shows
                    if (empty($c['permission']) || Gate::allows($c['permission'])) {
                        $parentShow = true;
                    }
                }
            } else {
                $parentShow = true;
                if (request()->is($parent['href']) || request()->is($parent['href'].'/*')) {
                    $active = true;
                }
            }
            if (! $parentShow) {
                continue;
            }
            ?>
            <li class="nav-item has-treeview <?php echo $active ? 'menu-open' : '' ?>">
                <a href="<?php echo url($parent['href']) ?>" class="nav-link <?php echo $active ? 'active' : '' ?>">
                    <i class="nav-icon <?php echo $parent['icon'] ?? 'fas fa-book' ?>"></i>
                    <p>
                        <?php echo $parent['title'] ?>
                        <?php if (! empty($parent['child']) > 0) { ?>
                            <i class="right fas fa-angle-left"></i>
                        <?php } ?>
                    </p>
                </a>
                <?php if (! empty($parent['child']) > 0) { ?>
                    <ul class="nav nav-treeview">
                        <?php foreach ($parent['child'] as $child) {
                            if (! empty($child['permission']) && ! Gate::allows($child['permission'])) {
                                continue;
                            }
                            $active = request()->is($child['href']) || request()->is($child['href'].'/*') ? true : false
                            ?>
                            <li class="nav-item">
                                <a href="<?php echo url($child['href']) ?>"
                                   class="nav-link <?php echo $active ? 'active' : '' ?>">
                                    <i class="<?php echo $child['icon'] ?? 'far fa-circle' ?> nav-icon"></i>
                                    <p><?php echo $child['title'] ?></p>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>
            </li>
        <?php }
        }
}
