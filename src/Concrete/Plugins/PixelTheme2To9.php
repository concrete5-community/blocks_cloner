<?php

namespace Concrete\Package\BlocksCloner\Plugins;

use Concrete\Package\BlocksCloner\Conversion\Environment;
use Concrete\Package\BlocksCloner\Converter;
use Concrete\Package\BlocksCloner\Plugin\ConvertExport;
use Concrete\Package\BlocksCloner\Plugin\ConvertImport;
use SimpleXMLElement;

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
        return [
            new Converter\Description('pixel2', t('Fix Pixel Theme v2 exports')),
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Package\BlocksCloner\Plugin\ConvertExport::applyExportConverterByHandle()
     */
    public function applyExportConverterByHandle(SimpleXMLElement $xDocument, $handle)
    {
        if ($handle !== 'pixel2') {
            return false;
        }
        $this->fixExport2($xDocument);

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Package\BlocksCloner\Plugin\ConvertExport::applyExportConvertersByEnvironment()
     */
    public function applyExportConvertersByEnvironment(SimpleXMLElement $xDocument, Environment $environment)
    {
        if (!preg_match('/^2(\.|$)/', $environment->getPackageVersion('theme_pixel'))) {
            return;
        }
        $this->fixExport2($xDocument);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Package\BlocksCloner\Plugin\ConvertImport::getImportConverters()
     */
    public function getImportConverters()
    {
        return [
            new Converter\Description('pixel2to9', t('Theme Pixel v2 to v9')),
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Package\BlocksCloner\Plugin\ConvertImport::applyImportConverterByHandle()
     */
    public function applyImportConverterByHandle(SimpleXMLElement $xDocument, $handle)
    {
        if ($handle !== 'pixel2to9') {
            return false;
        }
        $this->convert2to9($xDocument);

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Package\BlocksCloner\Plugin\ConvertImport::applyImportConvertersByEnvironment()
     */
    public function applyImportConvertersByEnvironment(SimpleXMLElement $xDocument, Environment $sourceEnvironment, Environment $targetEnvironment)
    {
        if (!preg_match('/^2(\.|$)/', $sourceEnvironment->getPackageVersion('theme_pixel')) || !preg_match('/^9(\.|$)/', $targetEnvironment->getPackageVersion('theme_pixel9'))) {
            return;
        }
        $this->convert2to9($xDocument);
    }

    private function fixExport2(SimpleXMLElement $xDocument)
    {
        $converter = new Converter($xDocument);
        $converter
            ->blocks('whale_cta')
                ->table('btWhaleCta')
                    ->fixExportedRichTextField('paragraph')
                ->done()
            ->done()
            ->blocks('whale_gallery')
                ->table('btWhaleGallery')
                    ->fixExportedFileSetIDField('fsID')
                ->done()
            ->done()
        ;
    }

    private function convert2to9(SimpleXMLElement $xDocument)
    {
        $converter = new Converter($xDocument);
        $converter
            ->blocks('autonav')
                ->changeCustomTemplate('pixel_breadcrumb', '')
                ->changeCustomTemplate('pixel_tree', '')
            ->done()
            ->blocks('content')
                ->changeCustomTemplate('pixel_fancy_title', 'pixel_page_title')
                ->changeCustomTemplate('pixel_fancy_title_bottom_border', 'pixel_page_title', 'title:bottom-border')
                ->changeCustomTemplate('pixel_fancy_title_bottom_border_short', 'pixel_page_title', 'title:bottom-border:short')
                ->changeCustomTemplate('pixel_fancy_title_center_aligned', '')
                ->changeCustomTemplate('pixel_fancy_title_emphasis', 'pixel_page_title', 'title:emphasis,title:dark,utl:text:align:center')
                ->changeCustomTemplate('pixel_fancy_title_left_aligned', '')
                ->changeCustomTemplate('pixel_fancy_title_left_aligned_border_double', '')
                ->changeCustomTemplate('pixel_fancy_title_left_border', 'pixel_page_title', 'title:left-border')
                ->changeCustomTemplate('pixel_fancy_title_right_aligned', 'pixel_page_title', 'title:bottom-border utl:text:align:end')
            ->done()
            ->blocks('date_navigation')
                ->changeCustomTemplate('pixel_list', '')
            ->done()
            ->blocks('express_entry_detail')
                ->changeCustomTemplate('pixel_team_member_grid', '')
                ->changeCustomTemplate('pixel_team_member_list', '')
                ->changeCustomTemplate('pixel_testimonial_grid', '')
                ->changeCustomTemplate('pixel_testimonial_list', '')
            ->done()
            ->blocks('express_entry_list')
                ->changeCustomTemplate('pixel_clients_carousel', 'pixel_client_carousel')
                ->changeCustomTemplate('pixel_clients_carousel_navigation', '')
                ->changeCustomTemplate('pixel_clients_carousel_pagination', '')
                ->changeCustomTemplate('pixel_clients_grid_2', 'pixel_client_grid')
                ->changeCustomTemplate('pixel_clients_grid_3', 'pixel_client_grid')
                ->changeCustomTemplate('pixel_clients_grid_4', 'pixel_client_grid')
                ->changeCustomTemplate('pixel_clients_grid_5', 'pixel_client_grid')
                ->changeCustomTemplate('pixel_clients_grid_6', 'pixel_client_grid')
                ->changeCustomTemplate('pixel_team_member_grid_3', 'pixel_team_member_grid')
                ->changeCustomTemplate('pixel_team_member_grid_4', 'pixel_team_member_grid')
                ->changeCustomTemplate('pixel_team_member_grid_carousel', 'pixel_team_member_carousel')
                ->changeCustomTemplate('pixel_team_member_list', '')
                ->changeCustomTemplate('pixel_team_member_list_carousel', 'pixel_team_member_carousel')
                ->changeCustomTemplate('pixel_testimonial_slider', '')
                ->changeCustomTemplate('pixel_testimonials_grid_2', 'pixel_testimonial_grid')
                ->changeCustomTemplate('pixel_testimonials_grid_3', 'pixel_testimonial_grid')
            ->done()
            ->blocks('faq')
                ->changeCustomTemplate('pixel_1', 'pixel_toggle')
                ->changeCustomTemplate('pixel_2', '')
                ->changeCustomTemplate('pixel_3', '')
                ->changeCustomTemplate('pixel_4_tabs', '')
            ->done()
            ->blocks('feature')
                ->changeCustomTemplate('pixel_alert', '')
                ->changeCustomTemplate('pixel_alert_bs', '')
                ->changeCustomTemplate('pixel_bordered', 'pixel_feature_box', 'fbox:layout:bordered')
                ->changeCustomTemplate('pixel_centered_icon', 'pixel_feature_box', 'fbox:layout:center')
                ->changeCustomTemplate('pixel_side_icon', 'pixel_feature_box')
            ->done()
            ->blocks('file')
                ->changeCustomTemplate('pixel', null, 'promo:border buttom:style:3d fileinfo:show-type fileinfo:show-size')
                ->changeCustomTemplate('pixel_color', 'pixel', 'promo-dark bg-color buttom:light fileinfo:show-type fileinfo:show-size')
                ->changeCustomTemplate('pixel_dark', 'pixel', 'promo:dark fileinfo:show-type fileinfo:show-size')
                ->changeCustomTemplate('pixel_light', 'pixel', 'promo:light buttom:dark fileinfo:show-type fileinfo:show-size')
            ->done()
            ->blocks('form')
                ->changeCustomTemplate('pixel_subscribe', '')
            ->done()
            ->blocks('horizontal_rule')
                ->changeCustomTemplate('pixel_center', 'pixel', 'divider:center icon:circle')
                ->changeCustomTemplate('pixel_center_border', 'pixel', 'divider:center icon:circle divider:border')
                ->changeCustomTemplate('pixel_center_border_short', 'pixel', 'divider:center icon:circle divider:sm divider:border')
                ->changeCustomTemplate('pixel_center_rounded', 'pixel', 'divider:center icon:circle divider:rounded')
                ->changeCustomTemplate('pixel_center_rounded_short', 'pixel', 'divider:center icon:circle divider:sm divider:rounded')
                ->changeCustomTemplate('pixel_center_short', 'pixel', 'divider:center icon:circle divider:sm')
                ->changeCustomTemplate('pixel_left', 'pixel', 'icon:circle')
                ->changeCustomTemplate('pixel_left_border', 'pixel', 'divider:left icon:circle divider:border')
                ->changeCustomTemplate('pixel_left_border_short', 'pixel', 'divider:left icon:circle divider:sm divider:border')
                ->changeCustomTemplate('pixel_left_rounded', 'pixel', 'divider:left icon:circle divider:rounded')
                ->changeCustomTemplate('pixel_left_rounded_short', 'pixel', 'divider:left icon:circle divider:sm divider:rounded')
                ->changeCustomTemplate('pixel_left_short', 'pixel', 'divider:left icon:circle divider:sm')
                ->changeCustomTemplate('pixel_right', 'pixel', 'divider:right icon:circle')
                ->changeCustomTemplate('pixel_right_border', 'pixel', 'divider:right icon:circle divider:border')
                ->changeCustomTemplate('pixel_right_border_short', 'pixel', 'divider:right icon:circle divider:sm divider:border')
                ->changeCustomTemplate('pixel_right_rounded', 'pixel', 'divider:right icon:circle divider:rounded')
                ->changeCustomTemplate('pixel_right_rounded_short', 'pixel', 'divider:right icon:circle divider:sm divider:rounded')
                ->changeCustomTemplate('pixel_right_short', 'pixel', 'divider:right icon:circle divider:sm')
            ->done()
            ->blocks('html')
                ->changeCustomTemplate('pixel_test_bordered', '')
                ->changeCustomTemplate('pixel_test', '')
                ->changeCustomTemplate('pixel_test-2', '')
            ->done()
            ->blocks('image_slider')
                ->changeCustomTemplate('pixel_owl', '')
            ->done()
            ->blocks('manual_nav')
                ->changeCustomTemplate('pixel_top_bar', '')
            ->done()
            ->blocks('next_previous')
                ->changeCustomTemplate('pixel_post_nav', '')
            ->done()
            ->blocks('page_attribute_display')
                ->changeCustomTemplate('pixel_image_responsive_full', 'pixel_image_responsive')
            ->done()
            ->blocks('page_list')
                ->changeCustomTemplate('pixel_blog_full', 'pixel_blog_grid', 'grid:1')
                ->changeCustomTemplate('pixel_blog_grid_2', 'pixel_blog_grid', 'grid:1 grid:sm:2')
                ->changeCustomTemplate('pixel_blog_grid_3', 'pixel_blog_grid', 'grid:1 grid:sm:2 grid:md:3')
                ->changeCustomTemplate('pixel_blog_grid_4', '')
                ->changeCustomTemplate('pixel_blog_horizontal_thumb_small_full', '')
                ->changeCustomTemplate('pixel_blog_horizontal_thumb_x_small_full', '')
                ->changeCustomTemplate('pixel_blog_horizontal_thumb_x_small_half', '')
                ->changeCustomTemplate('pixel_footer', '')
                ->changeCustomTemplate('pixel_portfolio_carousel', 'pixel_portfolio_carousel', 'grid:2 grid:md:3 grid:md:4')
                ->changeCustomTemplate('pixel_portfolio_carousel_hover', '')
                ->changeCustomTemplate('pixel_portfolio_grid_1', 'pixel_portfolio_grid', 'grid:1')
                ->changeCustomTemplate('pixel_portfolio_grid_1_alt', '')
                ->changeCustomTemplate('pixel_portfolio_grid_2', 'pixel_portfolio_grid', 'grid:1 grid:sm:2')
                ->changeCustomTemplate('pixel_portfolio_grid_3', 'pixel_portfolio_grid', 'grid:1 grid:sm:2 grid:md:3')
                ->changeCustomTemplate('pixel_portfolio_grid_4', 'pixel_portfolio_grid', 'grid:1 grid:sm:2 grid:md:4')
                ->changeCustomTemplate('pixel_portfolio_grid_5', '')
                ->changeCustomTemplate('pixel_portfolio_grid_6', 'pixel_portfolio_grid', 'grid:1 grid:sm:2 grid:md:6')
                ->changeCustomTemplate('pixel_top_bar', '')
            ->done()
            ->blocks('page_title')
                ->changeCustomTemplate('pixel_blog_title', 'pixel_blog_entry_title')
                ->changeCustomTemplate('pixel_fancy_title_bottom_border', 'pixel', 'title:bottom-border')
                ->changeCustomTemplate('pixel_fancy_title_bottom_border_short', 'pixel', 'title:bottom-border:short')
                ->changeCustomTemplate('pixel_fancy_title_center_aligned', 'pixel', 'title:fancy-border:double title:align:center')
                ->changeCustomTemplate('pixel_fancy_title_emphasis', '')
                ->changeCustomTemplate('pixel_fancy_title_left_aligned', 'pixel', 'title:fancy-border:single')
                ->changeCustomTemplate('pixel_fancy_title_left_aligned_border_double', 'pixel', 'title:fancy-border:double')
                ->changeCustomTemplate('pixel_fancy_title_left_border', 'pixel', 'title:left-border')
                ->changeCustomTemplate('pixel_fancy_title_right_aligned', 'pixel', 'title:fancy-border:double title:align:right')
                ->changeCustomTemplate('pixel_plain', '')
            ->done()
            ->blocks('search')
                ->changeCustomTemplate('pixel_grouped', '')
            ->done()
            ->blocks('social_links')
                ->changeCustomTemplate('pixel_footer_icons', '')
                ->changeCustomTemplate('pixel_top_bar', '')
            ->done()
            ->blocks('survey')
                ->changeCustomTemplate('pixel', '')
            ->done()
            ->blocks('testimonial')
                ->changeCustomTemplate('pixel_grid', 'pixel')
                ->changeCustomTemplate('pixel_list', 'pixel', 'layout:center')
            ->done()
            ->blocks('topic_list')
                ->changeCustomTemplate('pixel_filter', '')
                ->changeCustomTemplate('pixel_filter_js', '')
                ->changeCustomTemplate('pixel_list', '')
            ->done()
            ->blocks('video')
                ->changeCustomTemplate('pixel', '')
            ->done()
            ->blocks('whale_chart')
                ->renameBlockTypeHandle('pixel_pie_chart')
                ->table('btWhaleChart')
                    ->renameTable('btPixelPieChart')
                    ->deleteField('lineCap')
                    ->deleteField('scaleColor')
                    ->convertFontAwesome4to5Field('icon')
                ->done()
            ->done()
            ->blocks('whale_counter')
                ->changeCustomTemplate('pixel_large', '')
                ->changeCustomTemplate('pixel_small', '')
                ->renameBlockTypeHandle('pixel_counter')
                ->table('btWhaleCounter')
                    ->renameTable('btPixelCounter')
                    ->convertFontAwesome4to5Field('icon')
                ->done()
            ->done()
            ->blocks('whale_cta')
                ->changeCustomTemplate('pixel_btn', 'pixel_button')
                ->changeCustomTemplate('pixel_btn_3d', 'pixel_button', 'button:style:3d')
                ->changeCustomTemplate('pixel_btn_3d_right', 'pixel_button', 'button:style:3d utl:text:align:end')
                ->changeCustomTemplate('pixel_btn_border', 'pixel_button', 'button:border')
                ->changeCustomTemplate('pixel_btn_border_fill_effect', 'pixel_button', 'button-border button-fill')
                ->changeCustomTemplate('pixel_btn_border_fill_effect_right', 'pixel_button', 'button-border button-fill utl:text:align:end')
                ->changeCustomTemplate('pixel_btn_border_rightpixel_btn_border_right', 'pixel_button', 'button:border utl:text:align:end')
                ->changeCustomTemplate('pixel_btn_border_thin', 'pixel_button', 'button:border button:border-thin')
                ->changeCustomTemplate('pixel_btn_border_thin_right', 'pixel_button', 'button:border button:border-thin utl:text:align:end')
                ->changeCustomTemplate('pixel_btn_circle', 'pixel_button', 'button:circle')
                ->changeCustomTemplate('pixel_btn_circle_right', 'pixel_button', 'button:circle utl:text:align:end')
                ->changeCustomTemplate('pixel_btn_full', 'pixel_button', 'button:full')
                ->changeCustomTemplate('pixel_btn_reveal', 'pixel_button', 'button:reveal')
                ->changeCustomTemplate('pixel_btn_reveal_right', 'pixel_button', 'button:reveal utl:text:align:end')
                ->changeCustomTemplate('pixel_btn_rounded', 'pixel_button', 'button:rounded')
                ->changeCustomTemplate('pixel_btn_rounded_right', 'pixel_button', 'button:rounded utl:text:align:end')
                ->changeCustomTemplate('pixel_btn_simple_right', 'pixel_button', 'utl:text:align:end')
                ->changeCustomTemplate('pixel_image_feature_box', 'pixel_image_feature_box')
                ->changeCustomTemplate('pixel_image_feature_box_bordered', 'pixel_image_feature_box', 'ifb:border')
                ->changeCustomTemplate('pixel_image_feature_box_fancy_title', '')
                ->changeCustomTemplate('pixel_promo', 'pixel_promo')
                ->changeCustomTemplate('pixel_promo_border', 'pixel_promo', 'promo:border')
                ->changeCustomTemplate('pixel_promo_border_center', 'pixel_promo', 'promo:border utl:text:align:center')
                ->changeCustomTemplate('pixel_promo_border_right', 'pixel_promo', 'promo:border utl:text:align:end')
                ->changeCustomTemplate('pixel_promo_color', 'pixel_promo', 'promo:color:theme')
                ->changeCustomTemplate('pixel_promo_dark', 'pixel_promo', 'promo:dark')
                ->changeCustomTemplate('pixel_promo_full', 'pixel_promo', 'promo:full')
                ->changeCustomTemplate('pixel_promo_full_color', 'pixel_promo', 'promo:full promo:color:theme')
                ->changeCustomTemplate('pixel_promo_full_dark', 'pixel_promo', 'promo:full promo:dark')
                ->changeCustomTemplate('pixel_promo_full_light', 'pixel_promo', 'promo:full promo:light')
                ->changeCustomTemplate('pixel_promo_full_parallax', 'pixel_promo', 'promo:full promo:color:transparent')
                ->changeCustomTemplate('pixel_promo_light', 'pixel_promo', 'promo:light')
                ->changeCustomTemplate('pixel_top_bar_link', '')
                ->changeCustomTemplate('pixel_top_bar_link_color', '')
                ->changeCustomTemplate('pixel_top_bar_link_label', '')
                ->renameBlockTypeHandle('pixel_cta')
                ->table('btWhaleCta')
                    ->renameTable('btPixelCta')
                    ->convertFontAwesome4to5Field('icon')
                    ->addField('color', '')
                ->done()
            ->done()
            ->blocks('whale_gallery')
                ->renameBlockTypeHandle('pixel_gallery')
                ->table('btWhaleGallery')
                    ->renameTable('btPixelGallery')
                    ->addField('columnsSm', '0')
                    ->addField('columnsMd', '0')
                    ->addField('columnsLg', '0')
                    ->addField('columnsXl', '0')
                    ->addField('columnsXxl', '0')
                    ->addField('gutter', '0')
                    ->addField('cropImage', '0')
                    ->addField('maxWidth', '0')
                    ->addField('maxHeight', '0')
                ->done()
            ->done()
            ->blocks('whale_image_slider')
                ->renameBlockTypeHandle('pixel_slider')
                ->table('btWhaleImageSlider')
                    ->renameTable('btPixelSlider')
                    ->addField('heightXl', '0')
                    ->addField('slideNumbers', '0')
                    ->addField('autoplayOnce', '0')
                    ->addField('disableParallax', '0')
                    ->deleteField('scroller')
                    ->deleteField('heightXss')
                    ->deleteField('heightXs')
                ->done()
                ->table('btWhaleImageSliderEntries')
                    ->renameTable('btPixelSliderEntries')
                ->done()
            ->done()
            ->blocks('whale_manual_nav')
                ->changeCustomTemplate('pixel_btn', 'pixel_button')
                ->changeCustomTemplate('pixel_btn_3d', 'pixel_button', 'button:style:3d')
                ->changeCustomTemplate('pixel_btn_3d_right', 'pixel_button', 'button:style:3d utl:text:align:end')
                ->changeCustomTemplate('pixel_btn_border', 'pixel_button', 'button:border')
                ->changeCustomTemplate('pixel_btn_border_fill_effect', 'pixel_button', 'button-border button-fill')
                ->changeCustomTemplate('pixel_btn_border_fill_effect_right', 'pixel_button', 'button-border button-fill utl:text:align:end')
                ->changeCustomTemplate('pixel_btn_border_rightpixel_btn_border_right', 'pixel_button', 'button:border utl:text:align:end')
                ->changeCustomTemplate('pixel_btn_border_thin', 'pixel_button', 'button:border button:border-thin')
                ->changeCustomTemplate('pixel_btn_border_thin_right', 'pixel_button', 'button:border button:border-thin utl:text:align:end')
                ->changeCustomTemplate('pixel_btn_circle', 'pixel_button', 'button:circle')
                ->changeCustomTemplate('pixel_btn_circle_right', 'pixel_button', 'button:circle utl:text:align:end')
                ->changeCustomTemplate('pixel_btn_full', 'pixel_button', 'button:full')
                ->changeCustomTemplate('pixel_btn_reveal', 'pixel_button', 'button:reveal')
                ->changeCustomTemplate('pixel_btn_reveal_right', 'pixel_button', 'button:reveal utl:text:align:end')
                ->changeCustomTemplate('pixel_btn_rounded', 'pixel_button', 'button:rounded')
                ->changeCustomTemplate('pixel_btn_rounded_right', 'pixel_button', 'button:rounded utl:text:align:end')
                ->changeCustomTemplate('pixel_btn_simple_right', 'pixel_button', 'utl:text:align:end')
                ->changeCustomTemplate('pixel_image_feature_box', 'pixel_image_feature_box')
                ->changeCustomTemplate('pixel_image_feature_box_bordered', 'pixel_image_feature_box', 'ifb:border')
                ->changeCustomTemplate('pixel_image_feature_box_fancy_title', '')
                ->changeCustomTemplate('pixel_promo', 'pixel_promo')
                ->changeCustomTemplate('pixel_promo_border', 'pixel_promo', 'promo:border')
                ->changeCustomTemplate('pixel_promo_border_center', 'pixel_promo', 'promo:border utl:text:align:center')
                ->changeCustomTemplate('pixel_promo_border_right', 'pixel_promo', 'promo:border utl:text:align:end')
                ->changeCustomTemplate('pixel_promo_color', 'pixel_promo', 'promo:color:theme')
                ->changeCustomTemplate('pixel_promo_dark', 'pixel_promo', 'promo:dark')
                ->changeCustomTemplate('pixel_promo_full', 'pixel_promo', 'promo:full')
                ->changeCustomTemplate('pixel_promo_full_color', 'pixel_promo', 'promo:full promo:color:theme')
                ->changeCustomTemplate('pixel_promo_full_dark', 'pixel_promo', 'promo:full promo:dark')
                ->changeCustomTemplate('pixel_promo_full_light', 'pixel_promo', 'promo:full promo:light')
                ->changeCustomTemplate('pixel_promo_full_parallax', 'pixel_promo', 'promo:full promo:color:transparent')
                ->changeCustomTemplate('pixel_promo_light', 'pixel_promo', 'promo:light')
                ->changeCustomTemplate('pixel_top_bar_link', '')
                ->changeCustomTemplate('pixel_top_bar_link_color', '')
                ->changeCustomTemplate('pixel_top_bar_link_label', '')
                ->renameBlockTypeHandle('pixel_manual_nav')
                ->table('btWhaleManualNavPixel')
                    ->renameTable('btPixelManualNav')
                ->done()
            ->done()
            ->blocks('whale_pricing_table')
                ->renameBlockTypeHandle('pixel_horizontal', '')
                ->renameBlockTypeHandle('pixel_light', '', 'pricing:minimal')
                ->renameBlockTypeHandle('pixel_pricing_table')
                ->table('btWhalePricingTable')
                    ->renameTable('btPixelPricingTable')
                    ->convertFontAwesome4to5Field('icon')
                    ->deleteField('featured')
                ->done()
            ->done()
        ;
    }
}
