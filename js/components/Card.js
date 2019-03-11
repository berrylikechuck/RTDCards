import React from 'react';
import PropTypes from 'prop-types';

import Term from './Term';

const Card = props => (

    <div>
        <h2><a href={`/node/${props.id}`}>{props.title}</a></h2>
        <div dangerouslySetInnerHTML={{__html: props.description}} />
        <ul>
            {props.terms.map(term => <Term key={term.tid} tid={term.tid} name={term.name} />)}
        </ul>
        <div>
            <img src={`${props.image.path}`} alt={`${props.image.alt}`} />
        </div>
    </div>

);

Card.propTypes = {
    title: PropTypes.string.isRequired,
    tids: PropTypes.array
}

export default Card;
