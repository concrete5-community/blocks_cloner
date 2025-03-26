import {type CoreConverter} from '../Converter';

const converter: CoreConverter = {
  name: 'concrete5 v8 to ConcreteCMS v9',
  applicableTo: {
    sourceVersionConstraint: '^8',
    destinationVersionConstraint: '>=9',
  },
  blockTypes: {
    event_list: {
      addRecordFields: {
        btEventList: {
          titleFormat: 'h5',
        },
      },
    },
    express_entry_list: {
      addRecordFields: {
        btExpressEntryList: {
          titleFormat: 'h2',
        },
      },
    },
    feature: {
      addRecordFields: {
        btFeature: {
          titleFormat: 'h4',
        },
      },
      fontAwesome4to5Fields: {
        btFeature: ['icon'],
      },
    },
    google_map: {
      addRecordFields: {
        btGoogleMap: {
          titleFormat: 'h3',
        },
      },
    },
    page_list: {
      addRecordFields: {
        btPageList: {
          titleFormat: 'h5',
        },
      },
    },
    rss_displayer: {
      addRecordFields: {
        btRssDisplay: {
          titleFormat: 'h5',
        },
      },
    },
    tags: {
      addRecordFields: {
        btTags: {
          titleFormat: 'h5',
        },
      },
    },
    topic_list: {
      addRecordFields: {
        btTopicList: {
          titleFormat: 'h5',
        },
      },
    },
  },
};

export default converter;
