<?php

class ManagerFindi18nController extends Controller
{
	public function extractStringsForTranslation( $path, $instance, $in_templates = false )
	{
		// Parse .php files:
		$literals = array();

		if ( !$in_templates )
		{
			exec( "find * $path |grep .php$", $file_list );

		}
		else
		{
			exec( "find * $path |grep .tpl$", $file_list );
		}

		foreach ( $file_list as $file_path )
		{
			$tpl_text = shell_exec( "cat {$file_path}" );

			if ( !$in_templates )
			{
				// $this->translate functions
				preg_match_all( "/translate\s*\(\s*\'([^\']+)\'[^\)]*\)/", $tpl_text, $translate_single_quotes );
				preg_match_all( "/translate\s*\(\s*\"([^\"]+)\"[^\)]*\)/", $tpl_text, $translate_double_quotes );

				// I18N::getTranslation functions
				preg_match_all( "/getTranslation\s*\(\s*\'([^\']+)\'[^\)]*\)/", $tpl_text, $i18n_translate_single_quotes );
				preg_match_all( "/getTranslation\s*\(\s*\"([^\"]+)\"[^\)]*\)/", $tpl_text, $i18n_translate_double_quotes );

				// FlashMessages
				preg_match_all( "/FlasMessages::set\s*\(\s*\'([^\']+)\'[^\)]*\)/", $tpl_text, $flash_translate_single_quotes );
				preg_match_all( "/FlasMessages::set\s*\(\s*\"([^\"]+)\"[^\)]*\)/", $tpl_text, $flash_translate_double_quotes );

				$file_literals = array_unique( array_merge(
						$translate_single_quotes[1],
						$translate_double_quotes[1],
						$i18n_translate_single_quotes[1],
						$i18n_translate_double_quotes[1],
						$flash_translate_single_quotes[1],
						$flash_translate_double_quotes[1]
						) );
			}
			else
			{
				// {t}Search 'T' blocks{/t}
				preg_match_all( "/\{t([^\{\}]*)\}([^\{\}]+)\{\/t[^\}]*\}/", $tpl_text, $matches );
				$file_literals = array_unique( $matches[2] );
			}

			if ( preg_match("/{$instance}\/(.+)$/", $file_path, $matchs) )
			{
				$file_relative_path = $matchs[1];
			}

			foreach ( $file_literals as $literal )
			{
				if ( array_key_exists( $literal, $literals ) )
				{
					$literals[$literal] = ( $literals[$literal] . ", " . $file_relative_path );
				}
				else
				{
					$literals[$literal] = $file_relative_path;
				}
			}
		}
		return $literals;
	}

	public function getLiterals( $instance )
	{
		$path = Bootstrap::$application . "/$instance";

		// Parse all templates
		$literals_groups['tpl'] = $this->extractStringsForTranslation( "$path/templates", $instance, true );

		// Parse all models:
		$literals_groups['models'] = $this->extractStringsForTranslation( "$path/models", $instance, false );

		// Parse all controllers:
		$literals_groups['controllers'] = $this->extractStringsForTranslation( "$path/controllers", $instance, false );

		// Parse all form configs:
		$literals_groups['forms'] = $this->extractStringsForTranslation( "$path/config", $instance, false );

		// Smarty plugins:
		$libs_path = ROOT_PATH . '/libs/Smarty-2.6.26/plugins';
		$literals_groups['smarty'] = $this->extractStringsForTranslation( $libs_path, 'libs', false );

		$final_literals = array();

		foreach ( $literals_groups as $group )
		{
			foreach ( $group as $literal=>$relative_path )
			{
				if ( array_key_exists( $literal, $final_literals ) )
				{
					$final_literals[$literal] = ( $final_literals[$literal] . ", " . $relative_path );
				}
				else
				{
					$final_literals[$literal] = $relative_path;
				}
			}
		}

		return $final_literals;
	}

	public function build()
	{
		$this->setLayout( 'manager/findi18n.tpl' );

		$post = Filter::getInstance();
		$available_instances = $this->getFileSystemFiles( 'instances', true );
		$locales_available = array();
		foreach ( $available_instances as $inst )
		{
			$locales_available[$inst] = $this->getFilesystemFiles( "instances/$inst/locale" );
		}

		$this->assign( 'instances', $available_instances );
		$this->assign( 'locales', $locales_available );


		$charset = $post->getString( 'charset' );
		$this->assign( 'charset', ( $charset ? $charset : 'utf-8' ) );

		$this->assign( 'instance', 'default' );

		if ( $post->isSent( 'instance' ) )
		{
			$instance = $post->getString( 'instance' );
			$locale = $post->getString( 'locale' );

			$temp_lang = explode ( '_', $locale );
			$this->assign( 'language', $temp_lang[1] );

			$literals = $this->getLiterals( $instance );
			$this->assign( 'literals', $literals );

			$path = Bootstrap::$application . "/$instance";
			$translations_file = "$path/locale/$locale";
			if ( file_exists( $translations_file ) )
			{
				include "$translations_file";

				$missing = array();

				foreach( $literals as $key=>$relative_path )
				{
					if ( !isset( $translations[$key] ) )
					{
						$missing[$key]= $relative_path;
					}
				}

				$this->assign( 'missing', $missing );
				$this->assign( 'instance', $instance );
				$this->assign( 'locale', $locale );
			}
			else
			{
				$this->assign( 'error', "File $locale not available for <strong>$instance</strong>" );
			}
		}

	}

	/**
	 * Extracts from the filesystem all the files under a path.
	 * If the flag only_dirs is set to true returns only the directories names.
	 *
	 * @return array
	 */
	public function getFileSystemFiles( $relative_path, $only_dirs = false )
	{
		$files = array();

		// Extract directories:
		$iterator = new DirectoryIterator( ROOT_PATH . "/$relative_path" );

		foreach ( $iterator as $fileinfo )
		{
			$file = $fileinfo->getFilename();
			// Exclude .svn, .cache and any other file starting with .
			if ( 0 !== strpos( $file, '.' ) )
			{
				if ( $only_dirs )
				{
					if ( $fileinfo->isDir() ) $files[] = $file;
				}
				else
				{
					$files[] = $file;
				}
			}
		}
		return $files;

	}

}