import React from 'react';
import PropTypes from 'prop-types';

import Term from './Term';

const Card = (props) => {
  const bgImage = {
    backgroundImage: `url(${props.image.path})`,
    backgroundPosition: `center center`,
    backgroundRepeat: `no-repeat`,
    backgroundSize: `cover`,
  };

  const bgColor = {
    backgroundColor: `#DDD`,
  };

  return (
    <div className="card" style={props.image.path ? bgImage : bgColor}>
      <h2>
        <a href={`/node/${props.id}`}>{props.title}</a>
      </h2>
      <div dangerouslySetInnerHTML={{ __html: props.description }} />
      <ul>
        {props.terms.map((term) => (
          <Term key={term.tid} tid={term.tid} name={term.name} />
        ))}
      </ul>
    </div>
  );
};

Card.propTypes = {
  title: PropTypes.string.isRequired,
  tids: PropTypes.array,
};

export default Card;
