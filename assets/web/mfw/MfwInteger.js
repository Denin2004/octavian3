import React, {Component} from 'react';

import NumberFormat from 'react-number-format';

class MfwInteger extends Component {
    
    render() {
        return (
            <NumberFormat 
                value={this.props.value*1}
                thousandSeparator={window.mfwApp.numeral.thousandSeparator}
                displayType={this.props.displayType ? this.props.displayType: 'text'}/>
        )
    }
}

export default MfwInteger;