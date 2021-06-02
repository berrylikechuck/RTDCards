import React from 'react';
import PropTypes from 'prop-types';

const Term = (props) => (
  <li>
    <a href={`/taxonomy/term/${props.tid}`}>{props.name}</a>
  </li>
);

Term.propTypes = {
  tid: PropTypes.string.isRequired,
  name: PropTypes.string.isRequired,
};

export default Term;
