<?php

namespace TccUnifametro\Entities;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\{Remote\RemoteWebElement, WebDriverBy, WebDriverExpectedCondition};
use Illuminate\Support\Collection;

class Expandir extends DriverConnection
{
    private string $url = 'http://www.unifametro.edu.br/expandir/';

    public RemoteWebDriver $driverExpandir;

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

            if ($this->preventHasData()) {
                $courses->push($this->getCourses());
            }

            $this->clickNextMonth();
            $this->waitHideLoad();
        }

        return $courses->toArray();
    }

    private function getCourses(): array
    {
        $courses = collect($this->driverExpandir->findElements(WebDriverBy::cssSelector('.fam-expandir-lista .fam-expandir--row')))->map(function (RemoteWebElement $el, $key) {
            $courseLine = [];
            $heads = $this->getTableHeaders();

            foreach ($el->findElements(WebDriverBy::tagName('span')) as $spanKey => $span) {
                $key = strtolower(str_replace(' ','_', strtr($heads->get($spanKey), $this->caracteres())));

                if ($spanKey === 6) {
                    $courseLine[$key] = $span->findElement(WebDriverBy::cssSelector('span a'))->getAttribute('href');
                } else {
                    $courseLine[$key] = $span->getAttribute('innerHTML');
                }
            }

            return $courseLine;
        });

        return [
            'month'   => $this->getMonthPageCurrent(),
            'courses' => $courses->toArray(),
        ];
    }

    private function preventHasData(): bool
    {
        return count($this->driverExpandir->findElements(WebDriverBy::cssSelector('.fam-calendario__lista header h2'))) != 0;
    }

    private function getMonthPageCurrent(): string
    {
        return $this->driverExpandir->findElement(WebDriverBy::cssSelector('.fam-calendario__lista header h2'))->getText();
    }

    private function getTableHeaders(): Collection
    {
        return collect($this->driverExpandir->findElements(WebDriverBy::cssSelector('ul .fam-expandir--head > span')))->transform(fn ($head) => $head->getAttribute('innerHTML'));
    }

    private function findCountMonths(): int
    {
        return count($this->driverExpandir->findElements(WebDriverBy::cssSelector('.fam-calendario-owl .owl-item:not(.cloned) > li'))) - 5;
    }

    protected function setDriver(): void
    {
        $this->driverExpandir = $this->driver->get($this->url);
    }

    protected function execScript(): void
    {
        $this->driverExpandir->executeScript("$('#ifrmGibeeChat').css('display','none');");
    }

    private function waitHideLoad(): void
    {
        $this->driverExpandir->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::className('fam-calendario__load')));
    }

    private function clickNextMonth(): void
    {
        $this->driverExpandir->findElement(WebDriverBy::className('fam-calendario-nav--next'))->click();
    }
}
