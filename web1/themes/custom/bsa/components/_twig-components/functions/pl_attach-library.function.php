<?php
/**
 * @file
 * Add "attach_library" function for Pattern Lab.
 */

use Symfony\Component\Yaml\Yaml;
use \PatternLab\Config;

$function = new Twig_SimpleFunction('attach_library', function ($string) {
  // Get Library Name from string.
  $libraryName = substr($string, strpos($string, "/") + 1);

  // Find Library in libraries.yml file.
  $yamlFile = glob('*.libraries.yml');
  $yamlOutput = Yaml::parseFile($yamlFile[0]);

  // For each item in .libraries.yml file.
  foreach($yamlOutput as $key => $value) {

    // If the library exists.
    if ($key === $libraryName) {
      $files = $yamlOutput[$key]['js'];
      // For each file, create an async script to insert to the Twig component.
      foreach($files as $key => $jsPath) {
        $baseUrl = '/';
        if (Config::getOption("drupalThemeDir")) {
          $baseUrl = Config::getOption("drupalThemeDir");
        }
        //$scriptString = '<script src="' . $baseUrl . $key . '" " data-name="' . $libraryName . '"></script>';
        $scriptString = '<script src="' . $baseUrl . $key . '"></script>';
        $stringLoader = \PatternLab\Template::getStringLoader();
        $output = $stringLoader->render(array("string" => $scriptString, "data" => []));
        return $output;
      }
    }
  }
}, array('is_safe' => array('html')));