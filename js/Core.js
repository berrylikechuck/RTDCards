import React from 'react';
import PropTypes from 'prop-types';

import Term from './Term';

const Core = props => (

    <div>
        <h2><a href={`node/${props.id}`}>{props.title}</a></h2>
        <div>{props.description}</div>
        <ul>
            {props.terms.map(term => <Term key={term.tid} tid={term.tid} name={term.name} />)}
        </ul>
        <div>
            <img src={`${props.image.path}`} alt={`${props.image.alt}`} />
        </div>
    </div>

);

Core.propTypes = {
    title: PropTypes.string.isRequired,
    tids: PropTypes.array
}

export default Core;

