const WordsNinjaPack = require('wordsninja');
const WordsNinja = new WordsNinjaPack();
let string = process.argv[2];
let wordList = process.argv[3];
const myArray = wordList.split(",");

(async () => {
    await WordsNinja.loadDictionary(); // First load dictionary
    WordsNinja.addWords(myArray);
    let words = WordsNinja.splitSentence(string,
        {
            //camelCaseSplitter: true,  // Frist camel case spliting
            capitalizeFirstLetter: true,  // Capitalize first letter of result
            joinWords: true  // Join words
        });
    console.log(words);
})();
