import {type CoreConverter} from '../Converter';

const converter: CoreConverter = {
  name: 'concrete5 v8 to ConcreteCMS v9',
  handle: 'core_8_to_9',
  applicableTo: {
    sourceVersionConstraint: '^8',
    destinationVersionConstraint: '^9',
  },
};

export default converter;
