<?php
class StrategyExample {
    public function __construct() {
        $context = new Context(new ConcreteStrategyA());
        $context->execute();

        $context = new Context(new ConcreteStrategyB());
        $context->execute();

        $context = new Context(new ConcreteStrategyC());
        $context->execute();
    }
}

interface IStrategy {
    public function execute();
}

class ConcreteStrategyA implements IStrategy {
    public function execute() {
        echo "Called ConcreteStrategyA execute method\n";
    }
}

class ConcreteStrategyB implements IStrategy {
    public function execute() {
        echo "Called ConcreteStrategyB execute method\n";
    }
}

class ConcreteStrategyC implements IStrategy {
    public function execute() {
        echo "Called ConcreteStrategyC execute method\n";
    }
}

class Context {
    var $strategy;

    public function __construct(IStrategy $strategy) {
        $this->strategy = $strategy;
    }

    public function execute() {
        $this->strategy->execute();
    }
}

new StrategyExample;