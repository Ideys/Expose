<?php

namespace Ideys\Settings;

use Ideys\Content\Section\Entity\Section;

/**
 * App settings entity.
 */
class Settings
{
    /**
     * @var string
     */
    private $name = 'Ideys Expose';

    /**
     * @var string
     */
    private $description = 'Smart gallery';

    /**
     * @var string
     */
    private $authorName = 'Your Name';

    /**
     * @var string
     */
    private $subDomain = self::SUB_DOMAIN_WWW;

    const SUB_DOMAIN_ROOT   = 'root';
    const SUB_DOMAIN_WWW    = 'www';

    /**
     * @var array
     */
    private $languages = array('en');

    /**
     * @var string
     */
    private $maintenance = '0';

    /**
     * @var string
     */
    private $analyticsKey;

    /**
     * @var string
     */
    private $verificationKey;

    /**
     * @var string
     */
    private $mapsKey;

    /**
     * @var string
     */
    private $googleFonts;

    /**
     * @var string
     */
    private $layoutBackground = self::LAYOUT_BG_WHITE;

    const LAYOUT_BG_WHITE = 'white';
    const LAYOUT_BG_BLACK = 'black';

    /**
     * @var string
     */
    private $customStyle;

    /**
     * @var string
     */
    private $customJavascript;

    /**
     * @var string
     */
    private $adminLink = self::ADMIN_LINK_POS_CONTACT;

    const ADMIN_LINK_POS_CONTACT    = 'contact.section';
    const ADMIN_LINK_POS_MENU       = 'menu';
    const ADMIN_LINK_POS_DISABLED   = 'disabled';

    /**
     * @var string
     */
    private $contactContent = 'Contact me';

    /**
     * @var string
     */
    private $contactSection = self::CONTACT_SECTION_ENABLED;

    const CONTACT_SECTION_ENABLED   = 'enabled';
    const CONTACT_SECTION_NO_FORM   = 'no.form';
    const CONTACT_SECTION_DISABLED  = 'disabled';

    /**
     * @var string
     */
    private $contactSendToEmail;

    /**
     * @var string
     */
    private $menuPosition = self::MENU_POS_TOP;

    const MENU_POS_TOP  = 'top';
    const MENU_POS_LEFT = 'left';

    /**
     * @var string
     */
    private $hideMenuOnHomepage = '0';

    /**
     * @var string
     */
    private $shareFiles = '0';

    /**
     * @var string
     */
    private $newSectionDefaultVisibility = Section::VISIBILITY_PUBLIC;

    /**
     * Return yes / no choices for form selects.
     *
     * @return array
     */
    public static function getIOChoices()
    {
        return array(
            '1' => 'yes',
            '0' => 'no',
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Settings
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Settings
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthorName()
    {
        return $this->authorName;
    }

    /**
     * @param string $authorName
     *
     * @return Settings
     */
    public function setAuthorName($authorName)
    {
        $this->authorName = $authorName;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubDomain()
    {
        return $this->subDomain;
    }

    /**
     * @param string $subDomain
     *
     * @return Settings
     */
    public function setSubDomain($subDomain)
    {
        $this->subDomain = $subDomain;

        return $this;
    }

    /**
     * Return sub-domain choices for automatic redirection.
     *
     * @return array
     */
    public static function getSubDomainChoices()
    {
        return array(
            self::SUB_DOMAIN_ROOT => 'http://',
            self::SUB_DOMAIN_WWW  => 'http://www.',
        );
    }

    /**
     * @return array
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * @param array $languages
     *
     * @return Settings
     */
    public function setLanguages($languages)
    {
        $this->languages = $languages;

        return $this;
    }

    /**
     * Return available translation languages.
     *
     * @return array
     */
    public static function getLanguagesChoices()
    {
        return array(
            'en' => 'language.en',
            'fr' => 'language.fr',
        );
    }

    /**
     * @return string
     */
    public function getMaintenance()
    {
        return $this->maintenance;
    }

    /**
     * @param string $maintenance
     *
     * @return Settings
     */
    public function setMaintenance($maintenance)
    {
        $this->maintenance = $maintenance;

        return $this;
    }

    /**
     * @return string
     */
    public function getAnalyticsKey()
    {
        return $this->analyticsKey;
    }

    /**
     * @param string $analyticsKey
     *
     * @return Settings
     */
    public function setAnalyticsKey($analyticsKey)
    {
        $this->analyticsKey = $analyticsKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getVerificationKey()
    {
        return $this->verificationKey;
    }

    /**
     * @param string $verificationKey
     *
     * @return Settings
     */
    public function setVerificationKey($verificationKey)
    {
        $this->verificationKey = $verificationKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getMapsKey()
    {
        return $this->mapsKey;
    }

    /**
     * @param string $mapsKey
     *
     * @return Settings
     */
    public function setMapsKey($mapsKey)
    {
        $this->mapsKey = $mapsKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getGoogleFonts()
    {
        return $this->googleFonts;
    }

    /**
     * @param string $googleFonts
     *
     * @return Settings
     */
    public function setGoogleFonts($googleFonts)
    {
        $this->googleFonts = $googleFonts;

        return $this;
    }

    /**
     * @return string
     */
    public function getLayoutBackground()
    {
        return $this->layoutBackground;
    }

    /**
     * @param string $layoutBackground
     *
     * @return Settings
     */
    public function setLayoutBackground($layoutBackground)
    {
        $this->layoutBackground = $layoutBackground;

        return $this;
    }

    /**
     * Return layout background choices.
     *
     * @return array
     */
    public static function getLayoutBackgroundChoices()
    {
        return array(
            self::LAYOUT_BG_BLACK => 'site.background.black',
            self::LAYOUT_BG_WHITE => 'site.background.white',
        );
    }

    /**
     * @return string
     */
    public function getCustomStyle()
    {
        return $this->customStyle;
    }

    /**
     * @param string $customStyle
     *
     * @return Settings
     */
    public function setCustomStyle($customStyle)
    {
        $this->customStyle = $customStyle;

        return $this;
    }

    /**
     * @return string
     */
    public function getCustomJavascript()
    {
        return $this->customJavascript;
    }

    /**
     * @param string $customJavascript
     *
     * @return Settings
     */
    public function setCustomJavascript($customJavascript)
    {
        $this->customJavascript = $customJavascript;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdminLink()
    {
        return $this->adminLink;
    }

    /**
     * @param string $adminLink
     *
     * @return Settings
     */
    public function setAdminLink($adminLink)
    {
        $this->adminLink = $adminLink;

        return $this;
    }

    /**
     * Return Admin link position choices.
     *
     * @return array
     */
    public static function getAdminLinkChoices()
    {
        return array(
            self::ADMIN_LINK_POS_CONTACT    => 'admin.link.on.contact.section',
            self::ADMIN_LINK_POS_MENU       => 'admin.link.on.menu',
            self::ADMIN_LINK_POS_DISABLED   => 'admin.link.disabled',
        );
    }

    /**
     * @return string
     */
    public function getContactContent()
    {
        return $this->contactContent;
    }

    /**
     * @param string $contactContent
     *
     * @return Settings
     */
    public function setContactContent($contactContent)
    {
        $this->contactContent = $contactContent;

        return $this;
    }

    /**
     * @return string
     */
    public function getContactSection()
    {
        return $this->contactSection;
    }

    /**
     * @param string $contactSection
     *
     * @return Settings
     */
    public function setContactSection($contactSection)
    {
        $this->contactSection = $contactSection;

        return $this;
    }

    /**
     * Return contact displaying choices.
     *
     * @return array
     */
    public static function getContactSectionChoices()
    {
        return array(
            self::CONTACT_SECTION_ENABLED   => 'contact.enabled',
            self::CONTACT_SECTION_NO_FORM   => 'contact.no.form',
            self::CONTACT_SECTION_DISABLED  => 'contact.disabled',
        );
    }

    /**
     * @return string
     */
    public function getContactSendToEmail()
    {
        return $this->contactSendToEmail;
    }

    /**
     * @param string $contactSendToEmail
     *
     * @return Settings
     */
    public function setContactSendToEmail($contactSendToEmail)
    {
        $this->contactSendToEmail = $contactSendToEmail;

        return $this;
    }

    /**
     * @return string
     */
    public function getMenuPosition()
    {
        return $this->menuPosition;
    }

    /**
     * @param string $menuPosition
     *
     * @return Settings
     */
    public function setMenuPosition($menuPosition)
    {
        $this->menuPosition = $menuPosition;

        return $this;
    }

    /**
     * Return menu position choices.
     *
     * @return array
     */
    public static function getMenuPositionChoices()
    {
        return array(
            self::MENU_POS_TOP  => 'top',
            self::MENU_POS_LEFT => 'left',
        );
    }

    /**
     * @return string
     */
    public function getHideMenuOnHomepage()
    {
        return $this->hideMenuOnHomepage;
    }

    /**
     * @param string $hideMenuOnHomepage
     *
     * @return Settings
     */
    public function setHideMenuOnHomepage($hideMenuOnHomepage)
    {
        $this->hideMenuOnHomepage = $hideMenuOnHomepage;

        return $this;
    }

    /**
     * @return string
     */
    public function getShareFiles()
    {
        return $this->shareFiles;
    }

    /**
     * @param string $shareFiles
     *
     * @return Settings
     */
    public function setShareFiles($shareFiles)
    {
        $this->shareFiles = $shareFiles;

        return $this;
    }

    /**
     * @return string
     */
    public function getNewSectionDefaultVisibility()
    {
        return $this->newSectionDefaultVisibility;
    }

    /**
     * @param string $newSectionDefaultVisibility
     *
     * @return Settings
     */
    public function setNewSectionDefaultVisibility($newSectionDefaultVisibility)
    {
        $this->newSectionDefaultVisibility = $newSectionDefaultVisibility;

        return $this;
    }
}
