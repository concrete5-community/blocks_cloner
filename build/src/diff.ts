import {type ChangeObject, diffChars, diffWords, diffWordsWithSpace, diffLines, createTwoFilesPatch} from 'diff';

interface Diff {
  value: string;
  added: boolean;
  removed: boolean;
}

const TYPE = {
  PATCH: 'patch',
  CHARS: 'chars',
  WORDS: 'words',
  WORDS_WITH_SPACE: 'words-with-space',
  LINES: 'lines',
};

function patchToChanges(patch: string): Diff[] {
  let headerReached = false;

  return patch
    .split('\n')
    .filter((line: string): boolean => {
      if (line.startsWith('@@')) {
        headerReached = true;
      }
      return headerReached;
    })
    .map((line: string): Diff => {
      return {
        value: line + '\n',
        added: line.startsWith('+'),
        removed: line.startsWith('-'),
      };
    });
}

function create(type: keyof typeof TYPE, oldStr: string, newStr: string): Diff[] {
  switch (type) {
    case TYPE.CHARS:
      return diffChars(oldStr, newStr);
    case TYPE.WORDS:
      return diffWords(oldStr, newStr);
    case TYPE.WORDS_WITH_SPACE:
      return diffWordsWithSpace(oldStr, newStr);
    case TYPE.LINES:
      return diffLines(oldStr, newStr);
    case TYPE.PATCH:
      return patchToChanges(createTwoFilesPatch('old', 'new', oldStr, newStr));
    default:
      throw new Error(`Unknown diff type: ${type}`);
  }
}

export default {
  TYPE,
  create,
};
