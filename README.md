# Evaluable Compiler

This one is kinda cool, broh. You can do stuff, and some more.

## What's the point, duuuUUUOOOÂÂÂaaaUUUude?

Well, this allows you to write operations pretty much the way you want, and scope them to the exact environment you're
interested in. You're like God, only tinier and a bit too much into the computer science shit.

Let's say, for instance, that I want to write some simple expressions in a Yaml file, and interpret these
on the run. Just for fun.
```yaml
access-right: ADMIN || (LOGGED && CURRENT_USER['firstname'].CURRENT_USER['lastname'] = 'MarcelusWallace')
```

The thing is, you will have to declare all your operators yourself ; no pain, no gain, broh. But that also means that
you can develop pretty moche ANY operator you want. You've always dreamt of having the quaternary operator
```<a> ? <b> : <c> : <d>```
as in  "*if `a` is `true` then `b` else if `a` is `maybe` then `c` else`d` if I got the time, broh*"? Well, you can!

## How to use

Your `EvaluableCompiler` needs a `CompilationContext` ; no context, no chocolate, and we all know you love chocolate,
Philip.

Such a schwaggadazinor is actually a pretty simple structure: it must provide to the compiler:
- A regex pattern to recognize an "atom" (PSHIOUUUuuuuu pshhhhhiouUUUUU ATOMIC SA MERE EN PLEIN DOL DE BRETAGNE).
An atom is a special identifier, such as commonly `true`, `3.14`, or a variable name, even a cheesecake if you like.
- A regex pattern to recognize a prefix operator, ie one of these you don't even wait to write anything before you shove
it deep in your call stack.
Note that this allows you to use dynamic operators, like `++++++++++++ a`, for `a += 11`. Ya nev' know, pal.
- A regex pattern to recognize a infix operator, ie with one argument on the left side, just in case the right side has
nothing to say to the judge.

Of course, these patterns alone are not enough to make your butter, broh. And that's when it becomes interesting:
- You must implement method `CompilationContext::buildAtom()`, which will allow you to inject constants, but also your
favorite constants, but also variables depending on the RuntimeContext!
- Your compilation context must also be able to provide operator descriptors (prefix and infix), accordingly with the
respective patterns.
Such a descriptor has two roles: it defines the arity of the operator, therefore the way the expression will be parsed;
it also instantiate the evaluable, just as you chose it to, broh.
This will allow you to do pretty much anything, for example:

```php
class MyVeryTrumpContext implements CompilationContext
{
    public function getPrefixOperatorDescriptor(string $symbol): PrefixOperatorDescriptor
    {
        switch($symbol) {
            case '<Trump>': return new Wuabeulabeudapdaop;
            case ...
        }
    }
...
}

class Wuabeulabeudapdaop extends PrefixOperatorDescriptor
{
    public function __construct()
    {
        parent::__construct(new OperatorFixedArity(1, 0));
    }
    public function instantiate(array $args): Evaluable
    {
        return new class($args[0]) implements Evaluable {
            /** @var Evaluable */
            private $operand;
            public function __construct($operand)
            {
                $this->operand = $operand;
            }
            function evaluate(RuntimeContext $context = null) {
                $content = file_get_contents('http://trumpdonald.org/counter.json');
                $json = json_decode($content, true);
                $trumpCount = $json['counter'];
                return $this->operand->evaluate($context) > $trumpCount;
            }
        };
    }
}
```

Been looking for the `<Trump>` operator for quite a while, broh, it was about time it came to the market. Don't thank
me. Cheers.
