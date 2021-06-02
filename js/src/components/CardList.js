import React from 'react';
import Card from './Card';

const CardList = (props) => {
  let cards;

  if (props.data.length) {
    cards = props.data.map((card) => (
      <Card
        key={card.id}
        id={card.id}
        title={card.name}
        description={card.body}
        tids={card.tids}
        terms={card.terms}
        image={card.image}
      />
    ));
  }

  return <div className="cardsList">{cards}</div>;
};

export default CardList;
