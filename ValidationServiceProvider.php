<?php

namespace OkayBueno\Validation;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

/**
 * Class ValidationServiceProvider
 * @package OkayBueno\Validation
 */
class ValidationServiceProvider extends ServiceProvider
{
    private $configPath = '/config/validators.php';

    /**
     *
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.$this->configPath => config_path('validators.php'),
        ], 'validators');
    }


    /**
     *
     */
    public function register()
    {
        // merge default config
        $this->mergeConfigFrom(
            __DIR__.$this->configPath , 'validators'
        );

        // Bind the validators.
        $this->autoBindValidators();
    }


    /***
     *
     */
    private function autoBindValidators()
    {

        try
        {
            // Load config parameters needed.
            $validatorsBasePath = config( 'validators.validators_path' );
            $baseNamespace = rtrim( config( 'validators.validator_interfaces_namespace' ), '\\' ) . '\\';

            $folders = scandir( $validatorsBasePath );

            // Remove the first 2 directories: "." and "..".
            array_shift( $folders );
            array_shift( $folders );

            foreach( $folders as $folder )
            {
                $folderPath = $validatorsBasePath.'/'.$folder;
                $currentInterfaceNamespace = $baseNamespace.$folder.'\\';
                $currentImplementationNamespace = $currentInterfaceNamespace.'src';

                // Scan files within the folder.
                $validatorInterfacesInFolder = File::files( $folderPath );

                foreach( $validatorInterfacesInFolder as $validatorInterface )
                {
                    // For each file find the Interface and the implementation and bind them together.
                    $interfaceName = pathinfo( $validatorInterface, PATHINFO_FILENAME );

                    $commonName = str_replace( 'Interface', '', $interfaceName );
                    $interfaceFullClassName = $currentInterfaceNamespace.$interfaceName;

                    $fullClassName = $currentImplementationNamespace.'\\'.$commonName;

                    if ( class_exists( $fullClassName ) )
                    {
                        // Bind the class.
                        $this->app->bind( $interfaceFullClassName, function ( $app ) use ( $fullClassName )
                        {
                            return $app->make( $fullClassName );
                        });
                    }
                }
            }
        } catch( \Exception $e )
        {
            // Be quiet; Silence is golden.
        }
    }
}
