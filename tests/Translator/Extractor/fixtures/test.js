Translator.trans('test.single_quote');
Translator.trans("test.double_quote");
Translator.transChoice('choose.single_quote');
Translator.transChoice("choose.double_quote");

t.trans ( 'test.single_quote_with_spaces' )
t.trans (
    "test.double_quote_with_spaces"
);

t.transChoice (
    'choose.single_quote_with_spaces'
)

t.transChoice ( "choose.double_quote_with_spaces" , 2 )

t.trans('test.with_domain', 'other_domain')

t.trans('dynamic_key.' + type)
