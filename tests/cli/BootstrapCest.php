<?php
class BootstrapCest
{

    public function bootstrap(\CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox/tests/_data/');
        $I->executeCommand('bootstrap');
        $I->seeFileFound('codeception.yml');
        $this->checkFilesCreated($I);
        $I->seeInShellOutput('Building Actor classes for suites');
    }

    public function bootstrapWithNamespace(\CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox/tests/_data/');
        $I->executeCommand('bootstrap --namespace Generated');

        $I->seeInShellOutput('Building Actor classes for suites');
        $I->seeFileFound('codeception.yml');
        $I->seeInThisFile('namespace: Generated');
        $I->dontSeeInThisFile('namespace Generated\\');
        $this->checkFilesCreated($I);

        $I->seeFileFound('Acceptance.php', 'tests/_support/Helper');
        $I->seeInThisFile('namespace Generated\Helper;');

        $I->seeFileFound('AcceptanceTester.php', 'tests/_support');
        $I->seeInThisFile('namespace Generated;');
    }

    public function bootstrapWithActor(\CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox/tests/_data/');
        $I->executeCommand('bootstrap --actor Ninja');
        $I->seeFileFound('AcceptanceNinja.php','tests/_support/');
    }

    public function bootstrapEmpty(\CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox/tests/_data/');
        $I->executeCommand('bootstrap --empty');
        $I->dontSeeFileFound('tests/acceptance');
        $I->dontSeeFileFound('AcceptanceTester.php','tests/acceptance');
        $I->seeFileFound('codeception.yml');
    }

    protected function checkFilesCreated(\CliGuy $I)
    {
        $I->seeDirFound('tests/_support');
        $I->seeDirFound('tests/_data');
        $I->seeDirFound('tests/_output');
        $I->seeDirFound('tests/_envs');

        $I->seeFileFound('functional.suite.yml','tests');
        $I->seeFileFound('acceptance.suite.yml','tests');
        $I->seeFileFound('unit.suite.yml','tests');

        $I->seeFileFound('_bootstrap.php','tests/acceptance');
        $I->seeFileFound('_bootstrap.php','tests/functional');
        $I->seeFileFound('_bootstrap.php','tests/unit');

        $I->seeFileFound('AcceptanceTester.php','tests/_support');
        $I->seeFileFound('FunctionalTester.php','tests/_support');
        $I->seeFileFound('UnitTester.php','tests/_support');

        $I->seeFileFound('Acceptance.php','tests/_support/Helper');
        $I->seeFileFound('Functional.php','tests/_support/Helper');
        $I->seeFileFound('Unit.php','tests/_support/Helper');
    }

}
