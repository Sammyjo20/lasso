<?php

namespace Sammyjo20\Lasso\Tests\Helpers;

use Illuminate\Support\Facades\File;
use Sammyjo20\Lasso\Helpers\Zip;
use Sammyjo20\Lasso\Tests\TestCase;
use ZipArchive;

class ZipTest extends TestCase
{
    private $sourceDirectory;
    private $destinationDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sourceDirectory = __DIR__ . '/Support/Zip/Source';
        $this->destinationDirectory = __DIR__ . '/Support/Zip/Destination';

        $this->cleanUpPreviousArtifacts();
    }

    /** @test */
    public function it_can_add_a_single_file_from_a_source_directory_to_a_zip_file(): void
    {
        $sourceFile = ['SingleFile/logo.png'];
        $zipFile = $this->destinationDirectory . '/SingleFile.zip';

        $this->assertFileDoesNotExist($zipFile);

        (new Zip($zipFile))
            ->addFilesFromDirectory($this->sourceDirectory .'/SingleFile')
            ->closeZip();

        $this->assertFileExists($zipFile);

        $this->assertZipFileContains($sourceFile, $zipFile);
    }

    /** @test */
    public function it_adds_all_files_within_a_source_directory_to_a_zip_file(): void
    {
        $sourceFiles = [
            'MultipleFiles/1.txt',
            'MultipleFiles/2.txt',
            'MultipleFiles/3.txt',
        ];

        $zipFile = $this->destinationDirectory . '/MultiFile.zip';

        $this->assertFileDoesNotExist($zipFile);

        (new Zip($zipFile))
            ->addFilesFromDirectory($this->sourceDirectory .'/MultipleFiles')
            ->closeZip();

        $this->assertFileExists($zipFile);

        $this->assertZipFileContains($sourceFiles, $zipFile);
    }

    /** @test */
    public function it_adds_all_files_within_a_source_directory_including_sub_folders_to_a_zip_file(): void
    {
       $sourceFiles = [
            'FilesWithSubFolder/SubFolder/in_sub_folder.txt',
            'FilesWithSubFolder/SubFolder/logo_in_sub_folder.png',
            'FilesWithSubFolder/logo_in_root_folder.png',
            'FilesWithSubFolder/in_root_folder.txt',
        ];

        $zipFile = $this->destinationDirectory . '/WithSubFolder.zip';

        $this->assertFileDoesNotExist($zipFile);

        (new Zip($zipFile))
            ->addFilesFromDirectory($this->sourceDirectory .'/FilesWithSubFolder')
            ->closeZip();

        $this->assertFileExists($zipFile);

        $this->assertZipFileContains($sourceFiles, $zipFile);
    }
    
    private function assertZipFileContains(array $sourceFiles, string $destinationZipFile): void
    {
        $inspectZipFile = new ZipArchive();
        $inspectZipFile->open($destinationZipFile);

        collect($sourceFiles)->each(function (string $filePath) use ($inspectZipFile) {
            $relativePath = $this->getRelativePath($filePath);

            $this->assertSame(
                file_get_contents($this->sourceDirectory . '/' . $filePath),
                $inspectZipFile->getFromName($relativePath)
            );
        });
    }

    /**
     * Clean working directory at the start by purging files generated in previous tests,
     * to be able to inspect the files manually if needed after a specific test has run.
     */
    private function cleanUpPreviousArtifacts(): void
    {
        File::cleanDirectory($this->destinationDirectory);
    }

    /**
     * @param string $filePath
     * @return false|string
     *
     * Returns the relative path of source files, to be located in the zip file.
     */
    private function getRelativePath(string $filePath): string
    {
        // Find the root directory (first directory listed)
        $rootDirectory = explode('/', $filePath)[0];

        // Remove the root directory from the given path
        $normalizedPath = str_replace($rootDirectory, "", $filePath);

        // Remove preliminary forward slash "/Dir" -> "Dir"
        $relativePath = substr($normalizedPath, 1);

        return $relativePath;
    }
}
