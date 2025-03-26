import {type PackageConverter} from '../Converter';

const converter: PackageConverter = {
  name: 'Theme Pixel v2 to v9',
  applicableTo: {
    sourcePackageHandle: 'theme_pixel',
    sourceVersionConstraint: '^2',
    destinationPackageHandle: 'theme_pixel9',
    destinationVersionConstraint: '^9',
  },
  blockTypes: {
    autonav: {
      templateRemapping: {
        pixel_breadcrumb: '',
        pixel_tree: '',
      },
    },
    content: {
      templateRemapping: {
        pixel_fancy_title: 'pixel_page_title',
        pixel_fancy_title_bottom_border: {newTemplate: 'pixel_page_title', newCustomClasses: 'title:bottom-border'},
        pixel_fancy_title_bottom_border_short: {newTemplate: 'pixel_page_title', newCustomClasses: 'title:bottom-border:short'},
        pixel_fancy_title_center_aligned: '',
        pixel_fancy_title_emphasis: {newTemplate: 'pixel_page_title', newCustomClasses: 'title:emphasis,title:dark,utl:text:align:center'},
        pixel_fancy_title_left_aligned: '',
        pixel_fancy_title_left_aligned_border_double: '',
        pixel_fancy_title_left_border: {newTemplate: 'pixel_page_title', newCustomClasses: 'title:left-border'},
        pixel_fancy_title_right_aligned: {newTemplate: 'pixel_page_title', newCustomClasses: 'title:bottom-border utl:text:align:end'},
      },
    },
    date_navigation: {
      templateRemapping: {
        pixel_list: '',
      },
    },
    express_entry_detail: {
      templateRemapping: {
        pixel_team_member_grid: '',
        pixel_team_member_list: '',
        pixel_testimonial_grid: '',
        pixel_testimonial_list: '',
      },
    },
    express_entry_list: {
      templateRemapping: {
        pixel_clients_carousel: {newTemplate: 'pixel_client_carousel'},
        pixel_clients_carousel_navigation: '',
        pixel_clients_carousel_pagination: '',
        pixel_clients_grid_2: {newTemplate: 'pixel_client_grid'},
        pixel_clients_grid_3: {newTemplate: 'pixel_client_grid'},
        pixel_clients_grid_4: {newTemplate: 'pixel_client_grid'},
        pixel_clients_grid_5: {newTemplate: 'pixel_client_grid'},
        pixel_clients_grid_6: {newTemplate: 'pixel_client_grid'},
        pixel_team_member_grid_3: {newTemplate: 'pixel_team_member_grid'},
        pixel_team_member_grid_4: {newTemplate: 'pixel_team_member_grid'},
        pixel_team_member_grid_carousel: {newTemplate: 'pixel_team_member_carousel'},
        pixel_team_member_list: '',
        pixel_team_member_list_carousel: {newTemplate: 'pixel_team_member_carousel'},
        pixel_testimonial_slider: '',
        pixel_testimonials_grid_2: {newTemplate: 'pixel_testimonial_grid'},
        pixel_testimonials_grid_3: {newTemplate: 'pixel_testimonial_grid'},
      },
    },
    faq: {
      templateRemapping: {
        pixel_1: {newTemplate: 'pixel_toggle'},
        pixel_2: '',
        pixel_3: '',
        pixel_4_tabs: '',
      },
    },
    feature: {
      templateRemapping: {
        pixel_alert: '',
        pixel_alert_bs: '',
        pixel_bordered: {newTemplate: 'pixel_feature_box', newCustomClasses: 'fbox:layout:bordered'},
        pixel_centered_icon: {newTemplate: 'pixel_feature_box', newCustomClasses: 'fbox:layout:center'},
        pixel_side_icon: {newTemplate: 'pixel_feature_box'},
      },
    },
    file: {
      templateRemapping: {
        pixel: {newCustomClasses: 'promo:border buttom:style:3d fileinfo:show-type fileinfo:show-size'},
        pixel_color: {newTemplate: 'pixel', newCustomClasses: 'promo-dark bg-color buttom:light fileinfo:show-type fileinfo:show-size'},
        pixel_dark: {newTemplate: 'pixel', newCustomClasses: 'promo:dark fileinfo:show-type fileinfo:show-size'},
        pixel_light: {newTemplate: 'pixel', newCustomClasses: 'promo:light buttom:dark fileinfo:show-type fileinfo:show-size'},
      },
    },
    form: {
      templateRemapping: {
        pixel_subscribe: '',
      },
    },
    horizontal_rule: {
      templateRemapping: {
        pixel_center: {newTemplate: 'pixel', newCustomClasses: 'divider:center icon:circle'},
        pixel_center_border: {newTemplate: 'pixel', newCustomClasses: 'divider:center icon:circle divider:border'},
        pixel_center_border_short: {newTemplate: 'pixel', newCustomClasses: 'divider:center icon:circle divider:sm divider:border'},
        pixel_center_rounded: {newTemplate: 'pixel', newCustomClasses: 'divider:center icon:circle divider:rounded'},
        pixel_center_rounded_short: {newTemplate: 'pixel', newCustomClasses: 'divider:center icon:circle divider:sm divider:rounded'},
        pixel_center_short: {newTemplate: 'pixel', newCustomClasses: 'divider:center icon:circle divider:sm'},
        pixel_left: {newTemplate: 'pixel', newCustomClasses: 'icon:circle'},
        pixel_left_border: {newTemplate: 'pixel', newCustomClasses: 'divider:left icon:circle divider:border'},
        pixel_left_border_short: {newTemplate: 'pixel', newCustomClasses: 'divider:left icon:circle divider:sm divider:border'},
        pixel_left_rounded: {newTemplate: 'pixel', newCustomClasses: 'divider:left icon:circle divider:rounded'},
        pixel_left_rounded_short: {newTemplate: 'pixel', newCustomClasses: 'divider:left icon:circle divider:sm divider:rounded'},
        pixel_left_short: {newTemplate: 'pixel', newCustomClasses: 'divider:left icon:circle divider:sm'},
        pixel_right: {newTemplate: 'pixel', newCustomClasses: 'divider:right icon:circle'},
        pixel_right_border: {newTemplate: 'pixel', newCustomClasses: 'divider:right icon:circle divider:border'},
        pixel_right_border_short: {newTemplate: 'pixel', newCustomClasses: 'divider:right icon:circle divider:sm divider:border'},
        pixel_right_rounded: {newTemplate: 'pixel', newCustomClasses: 'divider:right icon:circle divider:rounded'},
        pixel_right_rounded_short: {newTemplate: 'pixel', newCustomClasses: 'divider:right icon:circle divider:sm divider:rounded'},
        pixel_right_short: {newTemplate: 'pixel', newCustomClasses: 'divider:right icon:circle divider:sm'},
      },
    },
    html: {
      templateRemapping: {
        pixel_test_bordered: '',
        pixel_test: '',
        'pixel_test-2': '',
      },
    },
    image_slider: {
      templateRemapping: {
        pixel_owl: '',
      },
    },
    manual_nav: {
      templateRemapping: {
        pixel_top_bar: '',
      },
    },
    next_previous: {
      templateRemapping: {
        pixel_post_nav: '',
      },
    },
    page_attribute_display: {
      templateRemapping: {
        pixel_image_responsive_full: {newTemplate: 'pixel_image_responsive'},
      },
    },
    page_list: {
      templateRemapping: {
        pixel_blog_full: {newTemplate: 'pixel_blog_grid', newCustomClasses: 'grid:1'},
        pixel_blog_grid_2: {newTemplate: 'pixel_blog_grid', newCustomClasses: 'grid:1 grid:sm:2'},
        pixel_blog_grid_3: {newTemplate: 'pixel_blog_grid', newCustomClasses: 'grid:1 grid:sm:2 grid:md:3'},
        pixel_blog_grid_4: '',
        pixel_blog_horizontal_thumb_small_full: '',
        pixel_blog_horizontal_thumb_x_small_full: '',
        pixel_blog_horizontal_thumb_x_small_half: '',
        pixel_footer: '',
        pixel_portfolio_carousel: {newTemplate: 'pixel_portfolio_carousel', newCustomClasses: 'grid:2 grid:md:3 grid:md:4'},
        pixel_portfolio_carousel_hover: '',
        pixel_portfolio_grid_1: {newTemplate: 'pixel_portfolio_grid', newCustomClasses: 'grid:1'},
        pixel_portfolio_grid_1_alt: '',
        pixel_portfolio_grid_2: {newTemplate: 'pixel_portfolio_grid', newCustomClasses: 'grid:1 grid:sm:2'},
        pixel_portfolio_grid_3: {newTemplate: 'pixel_portfolio_grid', newCustomClasses: 'grid:1 grid:sm:2 grid:md:3'},
        pixel_portfolio_grid_4: {newTemplate: 'pixel_portfolio_grid', newCustomClasses: 'grid:1 grid:sm:2 grid:md:4'},
        pixel_portfolio_grid_5: '',
        pixel_portfolio_grid_6: {newTemplate: 'pixel_portfolio_grid', newCustomClasses: 'grid:1 grid:sm:2 grid:md:6'},
        pixel_top_bar: '',
      },
    },
    page_title: {
      templateRemapping: {
        pixel_blog_title: {newTemplate: 'pixel_blog_entry_title'},
        pixel_fancy_title_bottom_border: {newTemplate: 'pixel', newCustomClasses: 'title:bottom-border'},
        pixel_fancy_title_bottom_border_short: {newTemplate: 'pixel', newCustomClasses: 'title:bottom-border:short'},
        pixel_fancy_title_center_aligned: {newTemplate: 'pixel', newCustomClasses: 'title:fancy-border:double title:align:center'},
        pixel_fancy_title_emphasis: '',
        pixel_fancy_title_left_aligned: {newTemplate: 'pixel', newCustomClasses: 'title:fancy-border:single'},
        pixel_fancy_title_left_aligned_border_double: {newTemplate: 'pixel', newCustomClasses: 'title:fancy-border:double'},
        pixel_fancy_title_left_border: {newTemplate: 'pixel', newCustomClasses: 'title:left-border'},
        pixel_fancy_title_right_aligned: {newTemplate: 'pixel', newCustomClasses: 'title:fancy-border:double title:align:right'},
        pixel_plain: '',
      },
    },
    search: {
      templateRemapping: {
        pixel_grouped: '',
      },
    },
    social_links: {
      templateRemapping: {
        pixel_footer_icons: '',
        pixel_top_bar: '',
      },
    },
    survey: {
      templateRemapping: {
        pixel: '',
      },
    },
    testimonial: {
      templateRemapping: {
        pixel_grid: {newTemplate: 'pixel'},
        pixel_list: {newTemplate: 'pixel', newCustomClasses: 'layout:center'},
      },
    },
    topic_list: {
      templateRemapping: {
        pixel_filter: '',
        pixel_filter_js: '',
        pixel_list: '',
      },
    },
    video: {
      templateRemapping: {
        pixel: '',
      },
    },
    whale_chart: {
      newBlockTypeHandle: 'pixel_pie_chart',
      removeRecordFields: {
        btWhaleChart: ['lineCap', 'scaleColor'],
      },
      fontAwesome4to5Fields: {
        btWhaleChart: ['icon'],
      },
      renameDataTables: {
        btWhaleChart: 'btPixelPieChart',
      },
    },
    whale_counter: {
      newBlockTypeHandle: 'pixel_counter',
      renameDataTables: {
        btWhaleCounter: 'btPixelCounter',
      },
      templateRemapping: {
        pixel_large: '',
        pixel_small: '',
      },
    },
    whale_cta: {
      newBlockTypeHandle: 'pixel_cta',
      renameDataTables: {
        btWhaleCta: 'btPixelCta',
      },
      fontAwesome4to5Fields: {
        btWhaleCta: ['icon'],
      },
      addRecordFields: {
        btPixelCta: {
          color: '',
        },
      },
      templateRemapping: {
        pixel_btn: {newTemplate: 'pixel_button'},
        pixel_btn_3d: {newTemplate: 'pixel_button', newCustomClasses: 'button:style:3d'},
        pixel_btn_3d_right: {newTemplate: 'pixel_button', newCustomClasses: 'button:style:3d utl:text:align:end'},
        pixel_btn_border: {newTemplate: 'pixel_button', newCustomClasses: 'button:border'},
        pixel_btn_border_fill_effect: {newTemplate: 'pixel_button', newCustomClasses: 'button-border button-fill'},
        pixel_btn_border_fill_effect_right: {newTemplate: 'pixel_button', newCustomClasses: 'button-border button-fill utl:text:align:end'},
        pixel_btn_border_rightpixel_btn_border_right: {newTemplate: 'pixel_button', newCustomClasses: 'button:border utl:text:align:end'},
        pixel_btn_border_thin: {newTemplate: 'pixel_button', newCustomClasses: 'button:border button:border-thin'},
        pixel_btn_border_thin_right: {newTemplate: 'pixel_button', newCustomClasses: 'button:border button:border-thin utl:text:align:end'},
        pixel_btn_circle: {newTemplate: 'pixel_button', newCustomClasses: 'button:circle'},
        pixel_btn_circle_right: {newTemplate: 'pixel_button', newCustomClasses: 'button:circle utl:text:align:end'},
        pixel_btn_full: {newTemplate: 'pixel_button', newCustomClasses: 'button:full'},
        pixel_btn_reveal: {newTemplate: 'pixel_button', newCustomClasses: 'button:reveal'},
        pixel_btn_reveal_right: {newTemplate: 'pixel_button', newCustomClasses: 'button:reveal utl:text:align:end'},
        pixel_btn_rounded: {newTemplate: 'pixel_button', newCustomClasses: 'button:rounded'},
        pixel_btn_rounded_right: {newTemplate: 'pixel_button', newCustomClasses: 'button:rounded utl:text:align:end'},
        pixel_btn_simple_right: {newTemplate: 'pixel_button', newCustomClasses: 'utl:text:align:end'},
        pixel_image_feature_box: {newTemplate: 'pixel_image_feature_box'},
        pixel_image_feature_box_bordered: {newTemplate: 'pixel_image_feature_box', newCustomClasses: 'ifb:border'},
        pixel_image_feature_box_fancy_title: '',
        pixel_promo: {newTemplate: 'pixel_promo'},
        pixel_promo_border: {newTemplate: 'pixel_promo', newCustomClasses: 'promo:border'},
        pixel_promo_border_center: {newTemplate: 'pixel_promo', newCustomClasses: 'promo:border utl:text:align:center'},
        pixel_promo_border_right: {newTemplate: 'pixel_promo', newCustomClasses: 'promo:border utl:text:align:end'},
        pixel_promo_color: {newTemplate: 'pixel_promo', newCustomClasses: 'promo:color:theme'},
        pixel_promo_dark: {newTemplate: 'pixel_promo', newCustomClasses: 'promo:dark'},
        pixel_promo_full: {newTemplate: 'pixel_promo', newCustomClasses: 'promo:full'},
        pixel_promo_full_color: {newTemplate: 'pixel_promo', newCustomClasses: 'promo:full promo:color:theme'},
        pixel_promo_full_dark: {newTemplate: 'pixel_promo', newCustomClasses: 'promo:full promo:dark'},
        pixel_promo_full_light: {newTemplate: 'pixel_promo', newCustomClasses: 'promo:full promo:light'},
        pixel_promo_full_parallax: {newTemplate: 'pixel_promo', newCustomClasses: 'promo:full promo:color:transparent'},
        pixel_promo_light: {newTemplate: 'pixel_promo', newCustomClasses: 'promo:light'},
        pixel_top_bar_link: '',
        pixel_top_bar_link_color: '',
        pixel_top_bar_link_label: '',
      },
    },
    whale_gallery: {
      newBlockTypeHandle: 'pixel_gallery',
      renameDataTables: {
        btWhaleGallery: 'btPixelGallery',
      },
      addRecordFields: {
        btWhaleGallery: {
          columnsSm: '0',
          columnsMd: '0',
          columnsLg: '0',
          columnsXl: '0',
          columnsXxl: '0',
          gutter: '0',
          cropImage: '0',
          maxWidth: '0',
          maxHeight: '0',
        },
      },
    },
    whale_image_slider: {
      newBlockTypeHandle: 'pixel_slider',
      renameDataTables: {
        btWhaleImageSlider: 'btPixelSlider',
        btWhaleImageSliderEntries: 'btPixelSliderEntries',
      },
      addRecordFields: {
        btWhaleImageSlider: {
          heightXl: '0',
          slideNumbers: '0',
          autoplayOnce: '0',
          disableParallax: '0',
        },
      },
      removeRecordFields: {
        btWhaleImageSlider: ['scroller', 'heightXss', 'heightXs'],
      },
    },
    whale_manual_nav: {
      newBlockTypeHandle: 'pixel_manual_nav',
      renameDataTables: {
        btWhaleManualNavPixel: 'btPixelManualNav',
      },
      templateRemapping: {
        pixel_btn: {newTemplate: 'pixel_button'},
        pixel_btn_3d: {newTemplate: 'pixel_button', newCustomClasses: 'button:style:3d'},
        pixel_btn_3d_right: {newTemplate: 'pixel_button', newCustomClasses: 'button:style:3d utl:text:align:end'},
        pixel_btn_border: {newTemplate: 'pixel_button', newCustomClasses: 'button:border'},
        pixel_btn_border_fill_effect: {newTemplate: 'pixel_button', newCustomClasses: 'button-border button-fill'},
        pixel_btn_border_fill_effect_right: {newTemplate: 'pixel_button', newCustomClasses: 'button-border button-fill utl:text:align:end'},
        pixel_btn_border_rightpixel_btn_border_right: {newTemplate: 'pixel_button', newCustomClasses: 'button:border utl:text:align:end'},
        pixel_btn_border_thin: {newTemplate: 'pixel_button', newCustomClasses: 'button:border button:border-thin'},
        pixel_btn_border_thin_right: {newTemplate: 'pixel_button', newCustomClasses: 'button:border button:border-thin utl:text:align:end'},
        pixel_btn_circle: {newTemplate: 'pixel_button', newCustomClasses: 'button:circle'},
        pixel_btn_circle_right: {newTemplate: 'pixel_button', newCustomClasses: 'button:circle utl:text:align:end'},
        pixel_btn_full: {newTemplate: 'pixel_button', newCustomClasses: 'button:full'},
        pixel_btn_reveal: {newTemplate: 'pixel_button', newCustomClasses: 'button:reveal'},
        pixel_btn_reveal_right: {newTemplate: 'pixel_button', newCustomClasses: 'button:reveal utl:text:align:end'},
        pixel_btn_rounded: {newTemplate: 'pixel_button', newCustomClasses: 'button:rounded'},
        pixel_btn_rounded_right: {newTemplate: 'pixel_button', newCustomClasses: 'button:rounded utl:text:align:end'},
        pixel_btn_simple_right: {newTemplate: 'pixel_button', newCustomClasses: 'utl:text:align:end'},
        pixel_image_feature_box: {newTemplate: 'pixel_image_feature_box'},
        pixel_image_feature_box_bordered: {newTemplate: 'pixel_image_feature_box', newCustomClasses: 'ifb:border'},
        pixel_image_feature_box_fancy_title: '',
        pixel_promo: {newTemplate: 'pixel_promo'},
        pixel_promo_border: {newTemplate: 'pixel_promo', newCustomClasses: 'promo:border'},
        pixel_promo_border_center: {newTemplate: 'pixel_promo', newCustomClasses: 'promo:border utl:text:align:center'},
        pixel_promo_border_right: {newTemplate: 'pixel_promo', newCustomClasses: 'promo:border utl:text:align:end'},
        pixel_promo_color: {newTemplate: 'pixel_promo', newCustomClasses: 'promo:color:theme'},
        pixel_promo_dark: {newTemplate: 'pixel_promo', newCustomClasses: 'promo:dark'},
        pixel_promo_full: {newTemplate: 'pixel_promo', newCustomClasses: 'promo:full'},
        pixel_promo_full_color: {newTemplate: 'pixel_promo', newCustomClasses: 'promo:full promo:color:theme'},
        pixel_promo_full_dark: {newTemplate: 'pixel_promo', newCustomClasses: 'promo:full promo:dark'},
        pixel_promo_full_light: {newTemplate: 'pixel_promo', newCustomClasses: 'promo:full promo:light'},
        pixel_promo_full_parallax: {newTemplate: 'pixel_promo', newCustomClasses: 'promo:full promo:color:transparent'},
        pixel_promo_light: {newTemplate: 'pixel_promo', newCustomClasses: 'promo:light'},
        pixel_top_bar_link: '',
        pixel_top_bar_link_color: '',
        pixel_top_bar_link_label: '',
      },
    },
    whale_pricing_table: {
      newBlockTypeHandle: 'pixel_pricing_table',
      renameDataTables: {
        btWhalePricingTable: 'btPixelPricingTable',
      },
      fontAwesome4to5Fields: {
        btWhalePricingTable: ['icon'],
      },
      removeRecordFields: {
        btWhalePricingTable: ['featured'],
      },
      templateRemapping: {
        pixel_horizontal: '',
        pixel_light: {newTemplate: '', newCustomClasses: 'pricing:minimal'},
      },
    },
  },
};

export default converter;
