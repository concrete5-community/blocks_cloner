<?php

namespace Concrete\Package\BlocksCloner\Plugins;

use Concrete\Package\BlocksCloner\Converter\ApplicableTo;
use Concrete\Package\BlocksCloner\Converter\Export;
use Concrete\Package\BlocksCloner\Converter\Import;
use Concrete\Package\BlocksCloner\Plugin\ConvertExport;
use Concrete\Package\BlocksCloner\Plugin\ConvertImport;

defined('C5_EXECUTE') or die('Access Denied.');

class PixelTheme2To9 implements ConvertExport, ConvertImport
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Package\BlocksCloner\Plugin\ConvertExport::getExportConverters()
     */
    public function getExportConverters()
    {
        $converter = new Export();
        $converter
            ->addBlockType(
                'whale_cta',
                Export\BlockType::create()
                    ->addContentField('btWhaleCta', ['paragraph'])
            )
            ->addBlockType(
                'whale_gallery',
                Export\BlockType::create()
                    ->addFileSetIDField('btWhaleGallery', ['fsID'])
            )
        ;

        return [$converter];
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Package\BlocksCloner\Plugin\ConvertImport::getImportConverters()
     */
    public function getImportConverters()
    {
        $converter = new Import(
            t('Theme Pixel v2 to v9'),
            new ApplicableTo\Packages('theme_pixel', '^2', 'theme_pixel9', '^9')
        );
        $converter
            ->addBlockType(
                'autonav',
                Import\BlockType::create()
                    ->addTemplateRemapping('pixel_breadcrumb', '')
                    ->addTemplateRemapping('pixel_tree', '')
            )
            ->addBlockType(
                'content',
                Import\BlockType::create()
                    ->addTemplateRemapping('pixel_fancy_title', 'pixel_page_title')
                    ->addTemplateRemapping('pixel_fancy_title_bottom_border', 'pixel_page_title', 'title:bottom-border')
                    ->addTemplateRemapping('pixel_fancy_title_bottom_border_short', 'pixel_page_title', 'title:bottom-border:short')
                    ->addTemplateRemapping('pixel_fancy_title_center_aligned', '')
                    ->addTemplateRemapping('pixel_fancy_title_emphasis', 'pixel_page_title', 'title:emphasis,title:dark,utl:text:align:center')
                    ->addTemplateRemapping('pixel_fancy_title_left_aligned', '')
                    ->addTemplateRemapping('pixel_fancy_title_left_aligned_border_double', '')
                    ->addTemplateRemapping('pixel_fancy_title_left_border', 'pixel_page_title', 'title:left-border')
                    ->addTemplateRemapping('pixel_fancy_title_right_aligned', 'pixel_page_title', 'title:bottom-border utl:text:align:end')
            )
            ->addBlockType(
                'date_navigation',
                Import\BlockType::create()
                    ->addTemplateRemapping('pixel_list', '')
            )
            ->addBlockType(
                'express_entry_detail',
                Import\BlockType::create()
                    ->addTemplateRemapping('pixel_team_member_grid', '')
                    ->addTemplateRemapping('pixel_team_member_list', '')
                    ->addTemplateRemapping('pixel_testimonial_grid', '')
                    ->addTemplateRemapping('pixel_testimonial_list', '')
            )
            ->addBlockType(
                'express_entry_list',
                Import\BlockType::create()
                    ->addTemplateRemapping('pixel_clients_carousel', 'pixel_client_carousel')
                    ->addTemplateRemapping('pixel_clients_carousel_navigation', '')
                    ->addTemplateRemapping('pixel_clients_carousel_pagination', '')
                    ->addTemplateRemapping('pixel_clients_grid_2', 'pixel_client_grid')
                    ->addTemplateRemapping('pixel_clients_grid_3', 'pixel_client_grid')
                    ->addTemplateRemapping('pixel_clients_grid_4', 'pixel_client_grid')
                    ->addTemplateRemapping('pixel_clients_grid_5', 'pixel_client_grid')
                    ->addTemplateRemapping('pixel_clients_grid_6', 'pixel_client_grid')
                    ->addTemplateRemapping('pixel_team_member_grid_3', 'pixel_team_member_grid')
                    ->addTemplateRemapping('pixel_team_member_grid_4', 'pixel_team_member_grid')
                    ->addTemplateRemapping('pixel_team_member_grid_carousel', 'pixel_team_member_carousel')
                    ->addTemplateRemapping('pixel_team_member_list', '')
                    ->addTemplateRemapping('pixel_team_member_list_carousel', 'pixel_team_member_carousel')
                    ->addTemplateRemapping('pixel_testimonial_slider', '')
                    ->addTemplateRemapping('pixel_testimonials_grid_2', 'pixel_testimonial_grid')
                    ->addTemplateRemapping('pixel_testimonials_grid_3', 'pixel_testimonial_grid')
            )
            ->addBlockType(
                'faq',
                Import\BlockType::create()
                    ->addTemplateRemapping('pixel_1', 'pixel_toggle')
                    ->addTemplateRemapping('pixel_2', '')
                    ->addTemplateRemapping('pixel_3', '')
                    ->addTemplateRemapping('pixel_4_tabs', '')
            )
            ->addBlockType(
                'feature',
                Import\BlockType::create()
                    ->addTemplateRemapping('pixel_alert', '')
                    ->addTemplateRemapping('pixel_alert_bs', '')
                    ->addTemplateRemapping('pixel_bordered', 'pixel_feature_box', 'fbox:layout:bordered')
                    ->addTemplateRemapping('pixel_centered_icon', 'pixel_feature_box', 'fbox:layout:center')
                    ->addTemplateRemapping('pixel_side_icon', 'pixel_feature_box')
            )
            ->addBlockType(
                'file',
                Import\BlockType::create()
                    ->addTemplateRemapping('pixel', null, 'promo:border buttom:style:3d fileinfo:show-type fileinfo:show-size')
                    ->addTemplateRemapping('pixel_color', 'pixel', 'promo-dark bg-color buttom:light fileinfo:show-type fileinfo:show-size')
                    ->addTemplateRemapping('pixel_dark', 'pixel', 'promo:dark fileinfo:show-type fileinfo:show-size')
                    ->addTemplateRemapping('pixel_light', 'pixel', 'promo:light buttom:dark fileinfo:show-type fileinfo:show-size')
            )
            ->addBlockType(
                'form',
                Import\BlockType::create()
                    ->addTemplateRemapping('pixel_subscribe', '')
            )
            ->addBlockType(
                'horizontal_rule',
                Import\BlockType::create()
                    ->addTemplateRemapping('pixel_center', 'pixel', 'divider:center icon:circle')
                    ->addTemplateRemapping('pixel_center_border', 'pixel', 'divider:center icon:circle divider:border')
                    ->addTemplateRemapping('pixel_center_border_short', 'pixel', 'divider:center icon:circle divider:sm divider:border')
                    ->addTemplateRemapping('pixel_center_rounded', 'pixel', 'divider:center icon:circle divider:rounded')
                    ->addTemplateRemapping('pixel_center_rounded_short', 'pixel', 'divider:center icon:circle divider:sm divider:rounded')
                    ->addTemplateRemapping('pixel_center_short', 'pixel', 'divider:center icon:circle divider:sm')
                    ->addTemplateRemapping('pixel_left', 'pixel', 'icon:circle')
                    ->addTemplateRemapping('pixel_left_border', 'pixel', 'divider:left icon:circle divider:border')
                    ->addTemplateRemapping('pixel_left_border_short', 'pixel', 'divider:left icon:circle divider:sm divider:border')
                    ->addTemplateRemapping('pixel_left_rounded', 'pixel', 'divider:left icon:circle divider:rounded')
                    ->addTemplateRemapping('pixel_left_rounded_short', 'pixel', 'divider:left icon:circle divider:sm divider:rounded')
                    ->addTemplateRemapping('pixel_left_short', 'pixel', 'divider:left icon:circle divider:sm')
                    ->addTemplateRemapping('pixel_right', 'pixel', 'divider:right icon:circle')
                    ->addTemplateRemapping('pixel_right_border', 'pixel', 'divider:right icon:circle divider:border')
                    ->addTemplateRemapping('pixel_right_border_short', 'pixel', 'divider:right icon:circle divider:sm divider:border')
                    ->addTemplateRemapping('pixel_right_rounded', 'pixel', 'divider:right icon:circle divider:rounded')
                    ->addTemplateRemapping('pixel_right_rounded_short', 'pixel', 'divider:right icon:circle divider:sm divider:rounded')
                    ->addTemplateRemapping('pixel_right_short', 'pixel', 'divider:right icon:circle divider:sm')
            )
            ->addBlockType(
                'html',
                Import\BlockType::create()
                    ->addTemplateRemapping('pixel_test_bordered', '')
                    ->addTemplateRemapping('pixel_test', '')
                    ->addTemplateRemapping('pixel_test-2', '')
            )
            ->addBlockType(
                'image_slider',
                Import\BlockType::create()
                    ->addTemplateRemapping('pixel_owl', '')
            )
            ->addBlockType(
                'manual_nav',
                Import\BlockType::create()
                    ->addTemplateRemapping('pixel_top_bar', '')
            )
            ->addBlockType(
                'next_previous',
                Import\BlockType::create()
                    ->addTemplateRemapping('pixel_post_nav', '')
            )
            ->addBlockType(
                'page_attribute_display',
                Import\BlockType::create()
                    ->addTemplateRemapping('pixel_image_responsive_full', 'pixel_image_responsive')
            )
            ->addBlockType(
                'page_list',
                Import\BlockType::create()
                    ->addTemplateRemapping('pixel_blog_full', 'pixel_blog_grid', 'grid:1')
                    ->addTemplateRemapping('pixel_blog_grid_2', 'pixel_blog_grid', 'grid:1 grid:sm:2')
                    ->addTemplateRemapping('pixel_blog_grid_3', 'pixel_blog_grid', 'grid:1 grid:sm:2 grid:md:3')
                    ->addTemplateRemapping('pixel_blog_grid_4', '')
                    ->addTemplateRemapping('pixel_blog_horizontal_thumb_small_full', '')
                    ->addTemplateRemapping('pixel_blog_horizontal_thumb_x_small_full', '')
                    ->addTemplateRemapping('pixel_blog_horizontal_thumb_x_small_half', '')
                    ->addTemplateRemapping('pixel_footer', '')
                    ->addTemplateRemapping('pixel_portfolio_carousel', 'pixel_portfolio_carousel', 'grid:2 grid:md:3 grid:md:4')
                    ->addTemplateRemapping('pixel_portfolio_carousel_hover', '')
                    ->addTemplateRemapping('pixel_portfolio_grid_1', 'pixel_portfolio_grid', 'grid:1')
                    ->addTemplateRemapping('pixel_portfolio_grid_1_alt', '')
                    ->addTemplateRemapping('pixel_portfolio_grid_2', 'pixel_portfolio_grid', 'grid:1 grid:sm:2')
                    ->addTemplateRemapping('pixel_portfolio_grid_3', 'pixel_portfolio_grid', 'grid:1 grid:sm:2 grid:md:3')
                    ->addTemplateRemapping('pixel_portfolio_grid_4', 'pixel_portfolio_grid', 'grid:1 grid:sm:2 grid:md:4')
                    ->addTemplateRemapping('pixel_portfolio_grid_5', '')
                    ->addTemplateRemapping('pixel_portfolio_grid_6', 'pixel_portfolio_grid', 'grid:1 grid:sm:2 grid:md:6')
                    ->addTemplateRemapping('pixel_top_bar', '')
            )
            ->addBlockType(
                'page_title',
                Import\BlockType::create()
                    ->addTemplateRemapping('pixel_blog_title', 'pixel_blog_entry_title')
                    ->addTemplateRemapping('pixel_fancy_title_bottom_border', 'pixel', 'title:bottom-border')
                    ->addTemplateRemapping('pixel_fancy_title_bottom_border_short', 'pixel', 'title:bottom-border:short')
                    ->addTemplateRemapping('pixel_fancy_title_center_aligned', 'pixel', 'title:fancy-border:double title:align:center')
                    ->addTemplateRemapping('pixel_fancy_title_emphasis', '')
                    ->addTemplateRemapping('pixel_fancy_title_left_aligned', 'pixel', 'title:fancy-border:single')
                    ->addTemplateRemapping('pixel_fancy_title_left_aligned_border_double', 'pixel', 'title:fancy-border:double')
                    ->addTemplateRemapping('pixel_fancy_title_left_border', 'pixel', 'title:left-border')
                    ->addTemplateRemapping('pixel_fancy_title_right_aligned', 'pixel', 'title:fancy-border:double title:align:right')
                    ->addTemplateRemapping('pixel_plain', '')
            )
            ->addBlockType(
                'search',
                Import\BlockType::create()
                    ->addTemplateRemapping('pixel_grouped', '')
            )
            ->addBlockType(
                'social_links',
                Import\BlockType::create()
                    ->addTemplateRemapping('pixel_footer_icons', '')
                    ->addTemplateRemapping('pixel_top_bar', '')
            )
            ->addBlockType(
                'survey',
                Import\BlockType::create()
                    ->addTemplateRemapping('pixel', '')
            )
            ->addBlockType(
                'testimonial',
                Import\BlockType::create()
                    ->addTemplateRemapping('pixel_grid', 'pixel')
                    ->addTemplateRemapping('pixel_list', 'pixel', 'layout:center')
            )
            ->addBlockType(
                'topic_list',
                Import\BlockType::create()
                    ->addTemplateRemapping('pixel_filter', '')
                    ->addTemplateRemapping('pixel_filter_js', '')
                    ->addTemplateRemapping('pixel_list', '')
            )
            ->addBlockType(
                'video',
                Import\BlockType::create()
                    ->addTemplateRemapping('pixel', '')
            )
            ->addBlockType(
                'whale_chart',
                Import\BlockType::create()
                    ->setNewBlockTypeHandle('pixel_pie_chart')
                    ->removeRecordFields('btWhaleChart', ['lineCap', 'scaleColor'])
                    ->fontAwesome4to5Fields('btWhaleChart', ['icon'])
                    ->renameDataTable('btWhaleChart', 'btPixelPieChart')
            )
            ->addBlockType(
                'whale_counter',
                Import\BlockType::create()
                    ->setNewBlockTypeHandle('pixel_counter')
                    ->renameDataTable('btWhaleCounter', 'btPixelCounter')
                    ->fontAwesome4to5Fields('btWhaleCounter', ['icon'])
                    ->addTemplateRemapping('pixel_large', '')
                    ->addTemplateRemapping('pixel_small', '')
            )
            ->addBlockType(
                'whale_cta',
                Import\BlockType::create()
                    ->setNewBlockTypeHandle('pixel_cta')
                    ->renameDataTable('btWhaleCta', 'btPixelCta')
                    ->fontAwesome4to5Fields('btWhaleCta', ['icon'])
                    ->addRecordFields('btPixelCta', [
                        'color' => '',
                    ])
                    ->addTemplateRemapping('pixel_btn', 'pixel_button')
                    ->addTemplateRemapping('pixel_btn_3d', 'pixel_button', 'button:style:3d')
                    ->addTemplateRemapping('pixel_btn_3d_right', 'pixel_button', 'button:style:3d utl:text:align:end')
                    ->addTemplateRemapping('pixel_btn_border', 'pixel_button', 'button:border')
                    ->addTemplateRemapping('pixel_btn_border_fill_effect', 'pixel_button', 'button-border button-fill')
                    ->addTemplateRemapping('pixel_btn_border_fill_effect_right', 'pixel_button', 'button-border button-fill utl:text:align:end')
                    ->addTemplateRemapping('pixel_btn_border_rightpixel_btn_border_right', 'pixel_button', 'button:border utl:text:align:end')
                    ->addTemplateRemapping('pixel_btn_border_thin', 'pixel_button', 'button:border button:border-thin')
                    ->addTemplateRemapping('pixel_btn_border_thin_right', 'pixel_button', 'button:border button:border-thin utl:text:align:end')
                    ->addTemplateRemapping('pixel_btn_circle', 'pixel_button', 'button:circle')
                    ->addTemplateRemapping('pixel_btn_circle_right', 'pixel_button', 'button:circle utl:text:align:end')
                    ->addTemplateRemapping('pixel_btn_full', 'pixel_button', 'button:full')
                    ->addTemplateRemapping('pixel_btn_reveal', 'pixel_button', 'button:reveal')
                    ->addTemplateRemapping('pixel_btn_reveal_right', 'pixel_button', 'button:reveal utl:text:align:end')
                    ->addTemplateRemapping('pixel_btn_rounded', 'pixel_button', 'button:rounded')
                    ->addTemplateRemapping('pixel_btn_rounded_right', 'pixel_button', 'button:rounded utl:text:align:end')
                    ->addTemplateRemapping('pixel_btn_simple_right', 'pixel_button', 'utl:text:align:end')
                    ->addTemplateRemapping('pixel_image_feature_box', 'pixel_image_feature_box')
                    ->addTemplateRemapping('pixel_image_feature_box_bordered', 'pixel_image_feature_box', 'ifb:border')
                    ->addTemplateRemapping('pixel_image_feature_box_fancy_title', '')
                    ->addTemplateRemapping('pixel_promo', 'pixel_promo')
                    ->addTemplateRemapping('pixel_promo_border', 'pixel_promo', 'promo:border')
                    ->addTemplateRemapping('pixel_promo_border_center', 'pixel_promo', 'promo:border utl:text:align:center')
                    ->addTemplateRemapping('pixel_promo_border_right', 'pixel_promo', 'promo:border utl:text:align:end')
                    ->addTemplateRemapping('pixel_promo_color', 'pixel_promo', 'promo:color:theme')
                    ->addTemplateRemapping('pixel_promo_dark', 'pixel_promo', 'promo:dark')
                    ->addTemplateRemapping('pixel_promo_full', 'pixel_promo', 'promo:full')
                    ->addTemplateRemapping('pixel_promo_full_color', 'pixel_promo', 'promo:full promo:color:theme')
                    ->addTemplateRemapping('pixel_promo_full_dark', 'pixel_promo', 'promo:full promo:dark')
                    ->addTemplateRemapping('pixel_promo_full_light', 'pixel_promo', 'promo:full promo:light')
                    ->addTemplateRemapping('pixel_promo_full_parallax', 'pixel_promo', 'promo:full promo:color:transparent')
                    ->addTemplateRemapping('pixel_promo_light', 'pixel_promo', 'promo:light')
                    ->addTemplateRemapping('pixel_top_bar_link', '')
                    ->addTemplateRemapping('pixel_top_bar_link_color', '')
                    ->addTemplateRemapping('pixel_top_bar_link_label', '')
            )
            ->addBlockType(
                'whale_gallery',
                Import\BlockType::create()
                    ->setNewBlockTypeHandle('pixel_gallery')
                    ->renameDataTable('btWhaleGallery', 'btPixelGallery')
                    ->addRecordFields('btWhaleGallery', [
                        'columnsSm' => '0',
                        'columnsMd' => '0',
                        'columnsLg' => '0',
                        'columnsXl' => '0',
                        'columnsXxl' => '0',
                        'gutter' => '0',
                        'cropImage' => '0',
                        'maxWidth' => '0',
                        'maxHeight' => '0',
                    ])
            )
            ->addBlockType(
                'whale_image_slider',
                Import\BlockType::create()
                    ->setNewBlockTypeHandle('pixel_slider')
                    ->renameDataTable('btWhaleImageSlider', 'btPixelSlider')
                    ->renameDataTable('btWhaleImageSliderEntries', 'btPixelSliderEntries')
                    ->addRecordFields('btWhaleImageSlider', [
                        'heightXl' => '0',
                        'slideNumbers' => '0',
                        'autoplayOnce' => '0',
                        'disableParallax' => '0',
                    ])
                    ->removeRecordFields('btWhaleImageSlider', ['scroller', 'heightXss', 'heightXs'])
            )
            ->addBlockType(
                'whale_manual_nav',
                Import\BlockType::create()
                    ->setNewBlockTypeHandle('pixel_manual_nav')
                    ->renameDataTable('btWhaleManualNavPixel', 'btPixelManualNav')
                    ->addTemplateRemapping('pixel_btn', 'pixel_button')
                    ->addTemplateRemapping('pixel_btn_3d', 'pixel_button', 'button:style:3d')
                    ->addTemplateRemapping('pixel_btn_3d_right', 'pixel_button', 'button:style:3d utl:text:align:end')
                    ->addTemplateRemapping('pixel_btn_border', 'pixel_button', 'button:border')
                    ->addTemplateRemapping('pixel_btn_border_fill_effect', 'pixel_button', 'button-border button-fill')
                    ->addTemplateRemapping('pixel_btn_border_fill_effect_right', 'pixel_button', 'button-border button-fill utl:text:align:end')
                    ->addTemplateRemapping('pixel_btn_border_rightpixel_btn_border_right', 'pixel_button', 'button:border utl:text:align:end')
                    ->addTemplateRemapping('pixel_btn_border_thin', 'pixel_button', 'button:border button:border-thin')
                    ->addTemplateRemapping('pixel_btn_border_thin_right', 'pixel_button', 'button:border button:border-thin utl:text:align:end')
                    ->addTemplateRemapping('pixel_btn_circle', 'pixel_button', 'button:circle')
                    ->addTemplateRemapping('pixel_btn_circle_right', 'pixel_button', 'button:circle utl:text:align:end')
                    ->addTemplateRemapping('pixel_btn_full', 'pixel_button', 'button:full')
                    ->addTemplateRemapping('pixel_btn_reveal', 'pixel_button', 'button:reveal')
                    ->addTemplateRemapping('pixel_btn_reveal_right', 'pixel_button', 'button:reveal utl:text:align:end')
                    ->addTemplateRemapping('pixel_btn_rounded', 'pixel_button', 'button:rounded')
                    ->addTemplateRemapping('pixel_btn_rounded_right', 'pixel_button', 'button:rounded utl:text:align:end')
                    ->addTemplateRemapping('pixel_btn_simple_right', 'pixel_button', 'utl:text:align:end')
                    ->addTemplateRemapping('pixel_image_feature_box', 'pixel_image_feature_box')
                    ->addTemplateRemapping('pixel_image_feature_box_bordered', 'pixel_image_feature_box', 'ifb:border')
                    ->addTemplateRemapping('pixel_image_feature_box_fancy_title', '')
                    ->addTemplateRemapping('pixel_promo', 'pixel_promo')
                    ->addTemplateRemapping('pixel_promo_border', 'pixel_promo', 'promo:border')
                    ->addTemplateRemapping('pixel_promo_border_center', 'pixel_promo', 'promo:border utl:text:align:center')
                    ->addTemplateRemapping('pixel_promo_border_right', 'pixel_promo', 'promo:border utl:text:align:end')
                    ->addTemplateRemapping('pixel_promo_color', 'pixel_promo', 'promo:color:theme')
                    ->addTemplateRemapping('pixel_promo_dark', 'pixel_promo', 'promo:dark')
                    ->addTemplateRemapping('pixel_promo_full', 'pixel_promo', 'promo:full')
                    ->addTemplateRemapping('pixel_promo_full_color', 'pixel_promo', 'promo:full promo:color:theme')
                    ->addTemplateRemapping('pixel_promo_full_dark', 'pixel_promo', 'promo:full promo:dark')
                    ->addTemplateRemapping('pixel_promo_full_light', 'pixel_promo', 'promo:full promo:light')
                    ->addTemplateRemapping('pixel_promo_full_parallax', 'pixel_promo', 'promo:full promo:color:transparent')
                    ->addTemplateRemapping('pixel_promo_light', 'pixel_promo', 'promo:light')
                    ->addTemplateRemapping('pixel_top_bar_link', '')
                    ->addTemplateRemapping('pixel_top_bar_link_color', '')
                    ->addTemplateRemapping('pixel_top_bar_link_label', '')
            )
            ->addBlockType(
                'whale_pricing_table',
                Import\BlockType::create()
                    ->setNewBlockTypeHandle('pixel_pricing_table')
                    ->renameDataTable('btWhalePricingTable', 'btPixelPricingTable')
                    ->fontAwesome4to5Fields('btWhalePricingTable', ['icon'])
                    ->removeRecordFields('btWhalePricingTable', ['featured'])
                    ->addTemplateRemapping('pixel_horizontal', '')
                    ->addTemplateRemapping('pixel_light', '', 'pricing:minimal')
            )
        ;

        return [$converter];
    }
}
