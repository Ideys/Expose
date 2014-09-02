<?php

namespace Ideys;

use Silex\Application as App;
use Silex\Provider\MonologServiceProvider;

/**
 * Useful hooks to inform IDE of objects
 * extracted from Silex application array.
 */
class SilexHooks
{
    /**
     * @param App $app
     *
     * @return \Silex\ControllerCollection
     */
    public static function controllerFactory(App $app) { return $app['controllers_factory']; }

    /**
     * @param App $app
     *
     * @return \Symfony\Component\Security\Core\SecurityContext
     */
    public static function security(App $app) { return $app['security']; }

    /**
     * @param App $app
     *
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
    public static function session(App $app) { return $app['session']; }

    /**
     * @param App $app
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public static function request(App $app) { return $app['request']; }

    /**
     * @param App $app
     *
     * @return \Symfony\Component\Routing\Generator\UrlGenerator
     */
    public static function urlGenerator(App $app) { return $app['url_generator']; }

    /**
     * @param App $app
     *
     * @return \Symfony\Component\Translation\Translator
     */
    public static function translator(App $app) { return $app['translator']; }

    /**
     * @param App $app
     *
     * @return \Twig_Environment
     */
    public static function twig(App $app) { return $app['twig']; }

    /**
     * @param App $app
     *
     * @return \Symfony\Component\Form\FormFactory
     */
    public static function formFactory(App $app) { return $app['form.factory']; }

    /**
     * @param App $app
     *
     * @return \Doctrine\DBAL\Connection
     */
    public static function db(App $app) { return $app['db']; }

    /**
     * @param App $app
     *
     * @return \Monolog\Logger
     */
    public static function monolog(App $app) { return $app['monolog']; }

    /**
     * Shortcut for redirect responses.
     *
     * @param App    $app
     * @param string $routeName  The name of controller route
     * @param array  $parameters The controller route parameters
     * @param string $hashTag    Optional hash tag for url (e.g.: #top)
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public static function redirect(App $app, $routeName, $parameters = array(), $hashTag = null)
    {
        return $app->redirect(static::urlGenerator($app)->generate($routeName, $parameters).$hashTag);
    }

    const FLASH_DEFAULT = 'default';
    const FLASH_ALERT   = 'alert';
    const FLASH_WARNING = 'warning';
    const FLASH_SUCCESS = 'success';

    /**
     * Shortcut for session flash bag add method.
     *
     * @param App    $app
     * @param string $message  The flash message
     * @param string $type     The flash message type
     */
    public static function flashMessage(App $app, $message, $type = self::FLASH_DEFAULT)
    {
        static::session($app)->getFlashBag()->add($type, static::translator($app)->trans($message));
    }

    /**
     * Shortcut to generate a standard form.
     * Useful for validation forms.
     *
     * @param App $app
     *
     * @return \Symfony\Component\Form\Form
     */
    public static function standardForm(App $app)
    {
        return static::formFactory($app)->createBuilder('form')->getForm();
    }
}
