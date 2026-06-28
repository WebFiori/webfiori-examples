# Event-Driven Architecture

Example application for the blog post: [Event-Driven Architecture with WebFiori](https://webfiori.com/blog/event-dispatcher)

## What This Demonstrates

- **Events** — plain PHP classes with readonly properties (`OrderPlacedEvent`, `UserRegisteredEvent`)
- **Listeners** — auto-discovered from `App/Listeners/` via typed `handle()` method
- **Multiple listeners per event** — decoupled reactions to the same trigger
- **Facade API** — `EventDispatcherFacade::dispatch()`, `listen()`, `getListeners()`
- **Direct instance** — `new EventDispatcher()` for isolated testing

## Running

```bash
composer install
composer test
```

## Related

- [Blog post](https://webfiori.com/blog/event-dispatcher)
- [Event Dispatcher docs](https://webfiori.com/docs/event-dispatcher)
