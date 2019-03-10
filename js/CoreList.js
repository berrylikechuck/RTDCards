import React from 'react';
import Core from './Core';

const CoreList = props => {

    //const results = props.data;
    let cores;
    if(props.data.length) {
        cores = props.data.map(core => <Core key={core.id} id={core.id} title={core.name} description={core.body} tids={core.tids} terms={core.terms} image={core.image} />);
    }

    return(
        <div>
            {cores}
        </div>
    );

}

export default CoreList;