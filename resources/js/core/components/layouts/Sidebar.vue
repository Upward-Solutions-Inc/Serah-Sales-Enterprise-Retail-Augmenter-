<template>
    <nav class="sidebar sidebar-offcanvas" id="sidebar">
        <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
            <a class="navbar-brand brand-logo" :href="logoUrl">
                <img :src="logoSrc" alt="logo"/>
            </a>
            <a class="navbar-brand brand-logo-mini" :href="logoUrl">
                <img :src="logoIconSrc" alt="logo"/>
            </a>
        </div>

        <ul class="nav nav-scrolling">
            <!-- 🔥 Render Default Sidebar Items -->
            <li class="nav-item" v-for="(item, index) in data" :key="'menu-' + index" v-if="item.permission">
                <a class="nav-link"
                   :href="item.id ? '#'+item.id : item.url"
                   :data-toggle="item.id ? 'collapse' : false"
                   :aria-expanded="item.id ? 'false' : false"
                   :aria-controls="item.id ? item.id : false">
                    <span class="icon-wrapper">
                        <app-icon :name="item.icon" class="menu-icon"/>
                    </span>
                    <span class="menu-title">{{ textTruncate(item.name) }}</span>
                    <i v-if="item.subMenu" class="menu-arrow"/>
                </a>

                <div class="collapse" v-if="item.subMenu" :id="item.id">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"
                            v-for="(submenuItem, index) in item.subMenu"
                            :key="'submenu-' + index"
                            v-if="submenuItem.permission === true || submenuItem.permission === ''">
                            <a class="nav-link" :href="submenuItem.url">
                                {{ textTruncate(submenuItem.name, 22) }}
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- 🔥 Inject Custom Menus from `localStorage.permissions.custom_menus` -->
            <li class="nav-item" v-for="(menu, index) in customMenus" :key="'custom-' + index" v-if="menu.permission">
                <a class="nav-link" :href="menu.url">
                    <span class="icon-wrapper">
                        <app-icon :name="menu.icon" class="menu-icon"/>
                    </span>
                    <span class="menu-title">{{ textTruncate(menu.name) }}</span>
                </a>
            </li>
        </ul>
    </nav>
</template>

<script>
import AppFunction from "../../helpers/app/AppFunction";

export default {
    name: "Sidebar",
    props: {
        data: {
            type: Array,
            required: true
        },
        logoSrc: {
            type: String,
            default: AppFunction.getAppUrl('images/core.png'),
        },
        logoIconSrc: {
            type: String,
            default: AppFunction.getAppUrl('images/core.png'),
        },
        logoUrl: {
            type: String,
            default: '/'
        }
    },
    data() {
        return {
            customMenus: [] // Store custom menus
        };
    },
    mounted() {
        // 🔥 Load custom menus from `localStorage.permissions`
        const permissions = JSON.parse(localStorage.getItem('permissions')) || {};
        this.customMenus = permissions.custom_menus || [];
        console.log("🔥 Custom Menus Loaded:", this.customMenus);

        this.$nextTick(function () {
            let body = $('body'),
                current = location.pathname,
                sidebar = $('.sidebar'),
                sidebarType = localStorage.getItem('sidebar');

            $(function () {
                function addActiveClass(element) {
                    if (element.attr('href').indexOf(current) !== -1) {
                        element.parents('.nav-item').last().addClass('active');
                        if (sidebarType === 'floating') {
                            if (element.parents('.sub-menu').length) {
                                element.closest('.collapse').removeClass('show');
                                element.addClass('active');
                            }
                            if (element.parents('.submenu-item').length) {
                                element.addClass('active');
                            }
                        } else {
                            if (element.parents('.sub-menu').length) {
                                element.closest('.collapse').addClass('show');
                                element.addClass('active');
                            }
                            if (element.parents('.submenu-item').length) {
                                element.addClass('active');
                            }
                        }
                    }
                }

                $('.nav li a', sidebar).each(function () {
                    let $this = $(this);
                    addActiveClass($this);
                });

                $('.horizontal-menu .nav li a').each(function () {
                    let $this = $(this);
                    addActiveClass($this);
                });

                sidebar.on('show.bs.collapse', '.collapse', function () {
                    sidebar.find('.collapse.show').collapse('hide');
                });

                applyStyles();

                function applyStyles() {
                    if (!body.hasClass("rtl")) {
                        if (body.hasClass("sidebar-fixed")) {
                            let fixedSidebarScroll = new PerfectScrollbar('#sidebar .nav');
                        }
                    }
                }
            });

            $(document).on('mouseenter mouseleave click', '.sidebar .nav-item', function (ev) {
                let body = $('body');
                let sidebarIconOnly = body.hasClass("sidebar-icon-only");
                let sidebarFixed = body.hasClass("sidebar-fixed");
                if (sidebarIconOnly) {
                    if (sidebarFixed) {
                        if (ev.type === 'mouseenter') {
                            body.removeClass('sidebar-icon-only');
                        }
                    } else {
                        let $menuItem = $(this);
                        if (ev.type === 'mouseenter') {
                            $menuItem.addClass('hover-open')
                        } else if (ev.type === 'click') {
                            $menuItem.addClass('hover-open')
                        } else {
                            $menuItem.removeClass('hover-open')
                        }
                    }
                }
            });

            $(document).on('mouseenter mouseleave', '.sidebar-hover-only .sidebar', function (ev) {
                if (ev.type === 'mouseleave') {
                    $('.sidebar').find('.collapse.show').collapse('hide');
                }
            });
        });
    },
    methods: {
        textTruncate(str, length = 18, ending = '...') {
            if (str.length > length) {
                return str.substring(0, length - ending.length) + ending;
            } else {
                return str;
            }
        }
    }
}
</script>
