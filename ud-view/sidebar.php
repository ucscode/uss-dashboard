<?php defined('UDASH_DIR') or die; ?>

<!-- ======== sidebar-nav start =========== -->

<aside class="sidebar-nav-wrapper">

	<div class="navbar-logo">
		<a href="%{udash.url}">
			<img src="<?php echo Uss::$global['icon']; ?>" height='50px' alt="logo" />
		</a>
	</div>
	
	<nav class="sidebar-nav text-capitalize">
		<ul id='nav-group'>
			<?php

                /**
                 * Enable dynamic menu updating
                 *
                 * @see menufy
                 */
                $renderMenu = function ($menufy, $renderMenu) {

                    $children = $menufy->child;

                    usort($children, function($a, $b) {
                        $orderA = (float)($a->getAttr('order') ?? 1);
                        $orderB = (float)($b->getAttr('order') ?? 1);
                        return ( $orderA <=> $orderB );
                    });

                    foreach($children as $menu):

                        /**
                         * @var $menu - A child menufy object
                         * @var $closure - Represents the same function that was passed to the `$menufy->iterate()` method
                         */

                        $limit = 3;

                        /**
                         * uss dashboard will not output menu that is more than 3 levels deep
                         *
                         * ```
                         * - level 1
                         * 	- level 2
                         * 	 - level 3
                         * 	  - level 4 ( uss dashboard will ignore this menu )
                         * ```
                         */
                        if(empty($menu->getAttr('label')) || $menu->level > $limit) {
                            return;
                        }

                        /**
                         * Each menu should have an id
                         * If ID is not specified, it will be auto-generated
                         */
                        preg_match_all("/\w+/i", $menu->name, $match);
                        $menu_id = implode('-', $match[0]);
                        $menu_id = $menu->getAttr('id') ?? "menufy" . ($menu->level ?: null) . "-{$menu_id}";

                        /**
                         * For <li/>
                         *
                         * Get the menu and submenu
                         * Menufy automatically count the depth (level) of menu
                         * This makes it easier to debug, style or apply custom feature
                         */

                        $li_attr = array(
                            "class" => !$menu->level ? 'nav-item' : 'nav-sub-item',
                            "data-menu-level" => $menu->level
                        );

                        /**
                         * Detect active menu
                         *
                         * Neither Menufy Object nor uss dashboard system automatically detects which menu is active
                         * The `active` attribute need to be specified with a boolean value `true` to indicate that the menu is active
                         */
                        $active = !empty($menu->getAttr('active'));

                        if($active) {
                            $li_attr['class'] .= ' active';
                        }

                        /**
                         * Detect leaf menu
                         *
                         * We need to detect which menu object has children
                         * And then add some HTML element attributes to it
                         */
                        if(!empty($menu->child) && $menu->level < $limit) {

                            # if menu has children

                            $li_attr['class'] .= " nav-item-has-children";

                            $anchor_attr = array(

                                /**
                                 * href will be converted to void
                                 * Because now, the menu will become a "dropdown"
                                 */
                                "href" => 'javascript:void(0)',

                                // class: The menu becomes collapsible
                                'class' => !$active ? 'collapsed' : null,

                                // data-bs-toggle: The menu can be toggled (bootstrap)
                                'data-bs-toggle' => 'collapse',

                                // data-bs-target: We're point to the menu dropdown `<ul/>` element
                                'data-bs-target' => "#{$menu_id}",

                                // whatever:
                                'aria-controls' => $menu_id,
                                'aria-expanded' => 'false'

                            );

                            /**
                             * Menu has no children
                             *
                             * If the menu has not child, it becomes a single clickable menu item
                             * Note: If a menu has children that exceeds the limited depth, the child will be discarded
                             */

                        } else {

                            $anchor_attr = array(
                                'href' => $menu->getAttr('href') ?? 'javascript:void(0)',
                                'target' => $menu->getAttr('target') ?? '_self'
                            );

                        };

                        /**
                         * Append Custom Attributes
                         */
                        $custom = $menu->getAttr('custom');
                        if(!is_array($custom)) {
                            $custom = [];
                        }

                        $anchor_attr += $custom;

                        /**
                         * Specify the root parent
                         *
                         * The root parent is required so that when one menu opens, another on closes
                         * Hence, preventing more than one dropdown from being open in the menu list
                         *
                         */
                        $parent = empty($menu->level) ? "data-bs-parent='#nav-group'" : null;

                        ?>

				<li <?php echo Core::array_to_html_attrs($li_attr); ?>>
				
					<a <?php echo Core::array_to_html_attrs($anchor_attr); ?>>
						<?php

                                if(empty($menu->level)):

                                    /**
                                     * Icon can be applied to the root menu through attribute
                                     * However, if icon need to be applied to a child menu,
                                     * The icon element should be prepended to the label attribute
                                     *
                                     * Example:
                                     *
                                     * ```php
                                     * $parentMenu->add('child', array(
                                     *   "label" => "<i class='bi bi-icon'></i> My label"
                                     * ));
                                     * ```
                                     *
                                     */

                                    ?>
							<span class="icon"><?php echo $menu->getAttr('icon'); ?></span>
						<?php endif; ?>
						<span class="text"><?php echo $menu->getAttr('label'); ?></span>
					</a>
					
					<?php
                                    /**
                                     * If the menu has children, then:
                                     */
                                    if(!empty($menu->child)):

                                        /**
                                         * Prepare a new `<ul/>` container
                                         * The child menu will be listed inside the container
                                         */
                                        ?>
					<ul id='<?php echo $menu_id; ?>' class='collapse dropdown-nav <?php if($active) {
					    echo 'show';
					} ?>' <?php echo $parent; ?>>
						<?php
                            /**
                             * Now, iterate over the child menu
                             */
                             $renderMenu($menu, $renderMenu);
                                        ?>
					</ul>
					<?php endif; ?>
					
				</li>
				
				<?php
                    if(!empty($menu->getAttr('hr'))):
                        /**
                         * Divider
                         *
                         * To add a horizontal rule below a menu,
                         * set `hr` attribute to `true`
                         */
                        ?>
					<span class="divider"><hr/></span>
				<?php endif; ?>
				
				<?php

                        /**
                         * Each menu will be ordered in ascending order
                         *
                         * Order are processed by menu name and not by menu label
                         * Hence, this will give developers the choice of positioning their menu where ever they what
                         */

                    endforeach;

                }; // end menu;
                
                $renderMenu(Uss::$global['menu'], $renderMenu);

            ?>
			
		</ul>
	</nav>
	
</aside>

<div class="overlay">
	<a href='javascript:void(0)' class='overlay-close'>	
		<i class='bi bi-x-lg'></i>
	</a>
</div>

<!-- ======== sidebar-nav end =========== -->