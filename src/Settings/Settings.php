<?php

namespace App\Settings;

use App\Content\Section\Section;

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
     * Main layout color.
     *
     * @var string
     */
    private $layoutColor = '#ffffff';

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
     */
    public static function getIOChoices(): array
    {
        return array(
            '1' => 'yes',
            '0' => 'no',
        );
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name = null): Settings
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description = null): Settings
    {
        $this->description = $description;

        return $this;
    }

    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    public function setAuthorName(string $authorName = null): Settings
    {
        $this->authorName = $authorName;

        return $this;
    }

    public function getSubDomain(): ?string
    {
        return $this->subDomain;
    }

    public function setSubDomain(string $subDomain = null): Settings
    {
        $this->subDomain = $subDomain;

        return $this;
    }

    /**
     * Return sub-domain choices for automatic redirection.
     */
    public static function getSubDomainChoices(): array
    {
        return array(
            self::SUB_DOMAIN_ROOT => 'http://',
            self::SUB_DOMAIN_WWW  => 'http://www.',
        );
    }

    public function getLanguages(): array
    {
        return $this->languages;
    }

    public function setLanguages(array $languages): Settings
    {
        $this->languages = $languages;

        return $this;
    }

    /**
     * Return available translation languages.
     */
    public static function getLanguagesChoices(): array
    {
        return array(
            'en' => 'language.en',
            'fr' => 'language.fr',
        );
    }

    public function getMaintenance(): ?string
    {
        return $this->maintenance;
    }

    public function setMaintenance(string $maintenance): Settings
    {
        $this->maintenance = $maintenance;

        return $this;
    }

    public function getAnalyticsKey(): ?string
    {
        return $this->analyticsKey;
    }

    public function setAnalyticsKey(string $analyticsKey = null): Settings
    {
        $this->analyticsKey = $analyticsKey;

        return $this;
    }
    public function getVerificationKey(): ?string
    {
        return $this->verificationKey;
    }

    public function setVerificationKey(string $verificationKey = null): Settings
    {
        $this->verificationKey = $verificationKey;

        return $this;
    }

    public function getMapsKey(): ?string
    {
        return $this->mapsKey;
    }

    public function setMapsKey(string $mapsKey = null): Settings
    {
        $this->mapsKey = $mapsKey;

        return $this;
    }

    public function getGoogleFonts(): ?string
    {
        return $this->googleFonts;
    }

    /**
     * @param string $googleFonts
     *
     * @return Settings
     */
    public function setGoogleFonts($googleFonts): Settings
    {
        $this->googleFonts = $googleFonts;

        return $this;
    }

    /**
     * @return string
     */
    public function getLayoutBackground(): ?string
    {
        return $this->layoutBackground;
    }

    /**
     * @param string $layoutBackground
     *
     * @return Settings
     */
    public function setLayoutBackground($layoutBackground): Settings
    {
        $this->layoutBackground = $layoutBackground;

        return $this;
    }

    /**
     * Return layout background choices.
     */
    public static function getLayoutBackgroundChoices(): array
    {
        return array(
            self::LAYOUT_BG_BLACK => 'site.background.black',
            self::LAYOUT_BG_WHITE => 'site.background.white',
        );
    }

    public function setLayoutColor(string $layoutColor): Settings
    {
        $this->layoutColor = $layoutColor;

        return $this;
    }

    public function getLayoutColor(): string
    {
        return $this->layoutColor;
    }

    public function getCustomStyle(): ?string
    {
        return $this->customStyle;
    }

    public function setCustomStyle(string $customStyle = null): Settings
    {
        $this->customStyle = $customStyle;

        return $this;
    }

    public function getCustomJavascript(): ?string
    {
        return $this->customJavascript;
    }

    public function setCustomJavascript(string $customJavascript = null): Settings
    {
        $this->customJavascript = $customJavascript;

        return $this;
    }

    public function getAdminLink(): ?string
    {
        return $this->adminLink;
    }

    public function setAdminLink(string $adminLink = null): Settings
    {
        $this->adminLink = $adminLink;

        return $this;
    }

    /**
     * Return Admin link position choices.
     */
    public static function getAdminLinkChoices(): array
    {
        return array(
            self::ADMIN_LINK_POS_CONTACT    => 'admin.link.on.contact.section',
            self::ADMIN_LINK_POS_MENU       => 'admin.link.on.menu',
            self::ADMIN_LINK_POS_DISABLED   => 'admin.link.disabled',
        );
    }

    public function getContactContent(): ?string
    {
        return $this->contactContent;
    }

    public function setContactContent($contactContent): Settings
    {
        $this->contactContent = $contactContent;

        return $this;
    }

    public function getContactSection(): ?string
    {
        return $this->contactSection;
    }

    public function setContactSection(string $contactSection): Settings
    {
        $this->contactSection = $contactSection;

        return $this;
    }

    /**
     * Return contact displaying choices.
     */
    public static function getContactSectionChoices(): array
    {
        return array(
            self::CONTACT_SECTION_ENABLED   => 'contact.enabled',
            self::CONTACT_SECTION_NO_FORM   => 'contact.no.form',
            self::CONTACT_SECTION_DISABLED  => 'contact.disabled',
        );
    }

    public function getContactSendToEmail(): ?string
    {
        return $this->contactSendToEmail;
    }

    public function setContactSendToEmail($contactSendToEmail): Settings
    {
        $this->contactSendToEmail = $contactSendToEmail;

        return $this;
    }

    public function getMenuPosition(): ?string
    {
        return $this->menuPosition;
    }

    public function setMenuPosition($menuPosition): Settings
    {
        $this->menuPosition = $menuPosition;

        return $this;
    }

    /**
     * Return menu position choices.
     */
    public static function getMenuPositionChoices(): array
    {
        return array(
            self::MENU_POS_TOP  => 'top',
            self::MENU_POS_LEFT => 'left',
        );
    }

    public function getHideMenuOnHomepage(): ?string
    {
        return $this->hideMenuOnHomepage;
    }

    public function setHideMenuOnHomepage($hideMenuOnHomepage): Settings
    {
        $this->hideMenuOnHomepage = $hideMenuOnHomepage;

        return $this;
    }

    public function getShareFiles(): ?string
    {
        return $this->shareFiles;
    }

    public function setShareFiles($shareFiles): Settings
    {
        $this->shareFiles = $shareFiles;

        return $this;
    }

    public function getNewSectionDefaultVisibility(): ?string
    {
        return $this->newSectionDefaultVisibility;
    }

    public function setNewSectionDefaultVisibility($newSectionDefaultVisibility): Settings
    {
        $this->newSectionDefaultVisibility = $newSectionDefaultVisibility;

        return $this;
    }
}
