<?php

declare(strict_types=1);

it('fixes testing class', function (): void {
    $this->fromFolder = realpath(__DIR__.'/../Fixtures/ClassesToTest');
    $this->fixedFolder = __DIR__.'/../../src/ClassesToTestFixed';

    mkdir($this->fixedFolder);
    copy($this->fromFolder.'/ClassWithTypoErrors.php', $this->fixedFolder.'/ClassWithTypoErrors.php');

    $dir = realpath(__DIR__.'/../../');
    shell_exec("cd {$dir} && ./bin/peck -s");

    $file_contents = file_get_contents($this->fixedFolder.'/ClassWithTypoErrors.php');

    $expected_file_contents = <<<'str_WRAP'
<?php

declare(strict_types=1);

namespace Tests\Fixtures\ClassesToTest;

/**
 * Class ClassWithTypoErrors
 *
 * This class is used to test type errors in class properties, methods, method parameters and class documentation block.
 *
 * @internal
 */
final class ClassWithTypoErrors
{
    public int $propertyWithoutTypoError = 1;

    public int $propertyWithTypoError = 2;

    /**
     * This is a property with a doc block typo error
     */
    public int $propertyWithDocBlockTypoError = 3;

    public function methodWithoutTypoError(): string
    {
        return 'This is a method without a typo error';
    }

    public function methodWithTypoError(): string
    {
        return 'This is a method with a typo error';
    }

    /**
     * This is a method with a doc block typo error
     */
    public function methodWithDocBlockTypoError(): string
    {
        return 'This is a method with a doc block typo error';
    }

    public function methodWithTypoErrorInParameters(string $parameterWithoutTypoError, string $parameterWithTypoError): string
    {
        return $parameterWithoutTypoError.$parameterWithTypoErorr.'This is a method with a typo error in parameters';
    }

    public function methodWithoutTypoErrorInParameters(string $parameterWithoutTypoError): string
    {
        return $parameterWithoutTypoError.'This is a method without a typo error in parameters';
    }
}

str_WRAP;

    expect($file_contents)->toBe($expected_file_contents);

    unlink($this->fixedFolder.'/ClassWithTypoErrors.php');
    rmdir($this->fixedFolder);
});

it('fixes folders', function (): void {
    $errorFolder = __DIR__.'/../../src/samplFolder';
    $fixedFolder = __DIR__.'/../../src/sampleFolder';

    mkdir($errorFolder);
    $dir = realpath(__DIR__.'/../../');
    shell_exec("cd {$dir} && ./bin/peck -s");

    expect(is_dir($fixedFolder))->toBeTrue();
    expect(is_dir($errorFolder))->toBeFalse();

    rmdir($fixedFolder);
});

it('fixes file names', function (): void {
    $errorFile = __DIR__.'/../../src/samplFile.php';
    $fixedFile = __DIR__.'/../../src/sampleFile.php';

    touch($errorFile);
    $dir = realpath(__DIR__.'/../../');
    shell_exec("cd {$dir} && ./bin/peck -s");

    expect(is_file($fixedFile))->toBeTrue();
    expect(is_file($errorFile))->toBeFalse();

    unlink($fixedFile);
});
