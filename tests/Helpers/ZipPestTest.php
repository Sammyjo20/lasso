<?php

use Illuminate\Support\Facades\File;
use Sammyjo20\Lasso\Helpers\Zip;
use function PHPUnit\Framework\assertFileDoesNotExist;
use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertStringEqualsFile;

beforeEach(function () {
    File::cleanDirectory(destinationDirectory());
});

test('it can add a single file from a source directory to a zip file', function () {
    $sourceFile = ['SingleFile/logo.png'];
    $zipFile = destinationDirectory() . '/SingleFile.zip';

    assertFileDoesNotExist($zipFile);

    (new Zip($zipFile))
        ->addFilesFromDirectory(sourceDirectory() . '/SingleFile')
        ->closeZip();

    assertFileExists($zipFile);
    assertZipFileContains($sourceFile, $zipFile);
});

test('it adds all files within a source directory to a zip file', function () {
    $sourceFiles = [
        'MultipleFiles/1.txt',
        'MultipleFiles/2.txt',
        'MultipleFiles/3.txt',
    ];

    $zipFile = destinationDirectory() . '/MultiFile.zip';

    assertFileDoesNotExist($zipFile);

    (new Zip($zipFile))
        ->addFilesFromDirectory(sourceDirectory() .'/MultipleFiles')
        ->closeZip();

    assertFileExists($zipFile);

    assertZipFileContains($sourceFiles, $zipFile);
});

test('it adds all files within a source directory including sub folders to a zip file', function () {
    $sourceFiles = [
        'FilesWithSubFolder/SubFolder/in_sub_folder.txt',
        'FilesWithSubFolder/SubFolder/logo_in_sub_folder.png',
        'FilesWithSubFolder/logo_in_root_folder.png',
        'FilesWithSubFolder/in_root_folder.txt',
    ];

    $zipFile = destinationDirectory() . '/WithSubFolder.zip';

    assertFileDoesNotExist($zipFile);

    (new Zip($zipFile))
        ->addFilesFromDirectory(sourceDirectory() .'/FilesWithSubFolder')
        ->closeZip();

    assertFileExists($zipFile);

    assertZipFileContains($sourceFiles, $zipFile);
});

function assertZipFileContains(array $sourceFiles, string $destinationZipFile): void
{
    $inspectZipFile = new ZipArchive();
    $inspectZipFile->open($destinationZipFile);

    collect($sourceFiles)->each(function (string $filePath) use ($inspectZipFile) {
        $relativePath = getRelativePath($filePath);

        assertStringEqualsFile(
            sourceDirectory() . '/' . $filePath, $inspectZipFile->getFromName($relativePath)
        );
    });
}

function getRelativePath(string $filePath): string
{
    // Find the root directory (first directory listed)
    $rootDirectory = explode('/', $filePath)[0];

    // Remove the root directory from the given path
    $normalizedPath = str_replace($rootDirectory, '', $filePath);

    // Remove preliminary forward slash "/Dir" -> "Dir"
    return substr($normalizedPath, 1);
}
