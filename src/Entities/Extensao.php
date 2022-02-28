<?php

namespace TccUnifametro\Entities;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\{Remote\RemoteWebElement, WebDriverBy, WebDriverExpectedCondition};
use Illuminate\Support\Collection;

class Extensao extends DriverConnection
{
    private string $url = 'http://www.unifametro.edu.br/extensao/';

    public RemoteWebDriver $driverEntensao;

    public function __construct()
    {
        $this->setup();
        $this->setDriver();
        $this->execScript();
    }

    public function run(): array
    {
        $courses = collect();

        for ($i = 1; $i <= $this->findCountMonths(); $i++) {
            $this->waitHideLoad();

            if (!$this->preventHasData()) {
                $courses->push($this->getCourses());
            }

            $this->clickNextMonth();
        }

        return $courses->toArray();
    }

    private function getCourses(): array
    {
        $courses = collect($this->driverEntensao->findElements(WebDriverBy::className('fam-expandir-lista')))->map(function (RemoteWebElement $courseEl) {
            $heads = $this->getTableHeaders();

            $coursesRows = collect($this->driverEntensao->findElements(WebDriverBy::cssSelector('.fam-expandir-lista .fam-expandir--row')))->map(function (RemoteWebElement $rowEl) use ($heads) {
                $row = [];

                foreach ($rowEl->findElements(WebDriverBy::tagName('span')) as $spanKey => $span) {
                    if ($spanKey === 6) {
                        $row[$heads->get($spanKey)] = $span->findElement(WebDriverBy::cssSelector('span a'))->getAttribute('href');
                    } else {
                        $row[$heads->get($spanKey)] = $span->getAttribute('innerHTML');
                    }
                }

                return $row;
            });

            return [
                'category'                  => $courseEl->findElement(WebDriverBy::tagName('h3'))->getText(),
                'coursesBelongsToCategory'  => $coursesRows->toArray(),
            ];
        });

        return [
            'month'    => $this->getMonthPageCurrent(),
            'courses'  => $courses->toArray(),
        ];
    }

    private function findCountMonths(): int
    {
        return count($this->driverEntensao->findElements(WebDriverBy::cssSelector('.fam-calendario-owl .owl-item:not(.cloned) > li'))) - 2;
    }

    private function getMonthPageCurrent(): string
    {
        return $this->driverEntensao->findElement(WebDriverBy::cssSelector('.fam-calendario__lista header .fam-headline__title'))->getText();
    }

    private function getTableHeaders(): Collection
    {
        return collect($this->driverEntensao->findElements(WebDriverBy::cssSelector('.fam-expandir--head span')))->transform(fn ($head) => $head->getAttribute('innerHTML'));
    }

    private function preventHasData(): bool
    {
        return count($this->driverEntensao->findElements(WebDriverBy::cssSelector('.fam-calendario__alert'))) != 0;
    }

    protected function setDriver(): void
    {
        $this->driverEntensao = $this->driver->get($this->url);
    }

    protected function execScript(): void
    {
        $this->driverEntensao->executeScript("$('#ifrmGibeeChat').css('display','none');");
    }

    private function waitHideLoad(): void
    {
        $this->driverEntensao->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('loading')));
    }

    private function clickNextMonth(): void
    {
        $this->driverEntensao->findElement(WebDriverBy::className('fam-calendario-nav--next'))->click();
    }
}
