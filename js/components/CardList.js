import React from 'react';
import Card from './Card';

const CardList = props => {

    //const results = props.data;
    let cards;
    if(props.data.length) {
        cards = props.data.map(card => <Card key={card.id} id={card.id} title={card.name} description={card.body} tids={card.tids} terms={card.terms} image={card.image} />);
    }

    return(
        <div>
            {cards}
        </div>
    );

}

export default CardList;
