import React, {Component} from 'react';

import NumberFormat from 'react-number-format';

class MfwNumber extends Component {
    
    render() {
        return (
            <NumberFormat 
                value={this.props.value*1}
                thousandSeparator={window.mfwApp.numeral.thousandSeparator}
                decimalSeparator={window.mfwApp.numeral.decimalSeparator}
                decimalScale={this.props.decimals ? this.props.decimals : window.mfwApp.numeral.decimals}
                fixedDecimalScale={true}
                displayType={this.props.displayType ? this.props.displayType: 'text'}/>
        )
    }
}

export default MfwNumber;