<?php

namespace Rx\React\Operator;

use Rx\Disposable\CompositeDisposable;
use Rx\ObservableInterface;
use Rx\Observer\CallbackObserver;
use Rx\ObserverInterface;
use Rx\Operator\OperatorInterface;
use Rx\SchedulerInterface;

/**
 * Cuts the stream based upon a delimiter.
 *
 * Class CutOperator
 * @package Rx\React\Operator
 */
class CutOperator implements OperatorInterface
{

    private $delimiter;

    public function __construct($delimiter = PHP_EOL)
    {
        $this->delimiter = $delimiter;
    }

    /**
     * @param \Rx\ObservableInterface $observable
     * @param \Rx\ObserverInterface $observer
     * @param \Rx\SchedulerInterface $scheduler
     * @return \Rx\DisposableInterface
     */
    public function __invoke(ObservableInterface $observable, ObserverInterface $observer, SchedulerInterface $scheduler = null)
    {
        $buffer     = '';
        $items      = [];
        $disposable = new CompositeDisposable();

        $onNext = function ($x) use (&$buffer, $observer, $scheduler, &$items, $disposable) {
            $buffer .= $x;
            $items  = array_merge($items, explode($this->delimiter, $buffer));
            $buffer = array_pop($items);

            $action = function ($reschedule) use (&$observer, &$items, &$buffer) {

                if (count($items) === 0) {
                    return;
                }

                $value = array_shift($items);

                $observer->onNext($value);

                $reschedule();

            };

            $schedulerDisposable = $scheduler->scheduleRecursive($action);

            $disposable->add($schedulerDisposable);
        };

        $onCompleted = function () use (&$buffer, $observer) {
            if (!empty($buffer)) {
                $observer->onNext($buffer);
            }
            $observer->onCompleted();
        };

        $callbackObserver = new CallbackObserver($onNext, [$observer, "onError"], $onCompleted);
        $sourceDisposable = $observable->subscribe($callbackObserver);

        $disposable->add($sourceDisposable);

        return $disposable;
    }
}
