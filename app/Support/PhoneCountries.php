<?php

namespace App\Support;

/**
 * PhoneCountries — ONE authoritative global country registry for mobile
 * phone verification (ITU-T E.164 dialing codes + ISO 3166-1 codes).
 *
 * Used by every phone/country selector (verification, registration,
 * profiles, admin user management) so dial codes never drift between
 * pages. This registry is for PHONE purposes only — billing currencies
 * stay in the countries DB table via CustomerCurrencyService.
 *
 * Entry: [name, alpha-2, alpha-3, dial code without "+", flag].
 * Flag is the alpha-3 text badge matching the existing UI convention
 * (no emoji dependency, readable in both themes).
 */
class PhoneCountries
{
    public static function all(): array
    {
        return [
            ['name' => 'Afghanistan', 'alpha2' => 'AF', 'alpha3' => 'AFG', 'dial' => '93', 'flag' => 'AFG'],
            ['name' => 'Albania', 'alpha2' => 'AL', 'alpha3' => 'ALB', 'dial' => '355', 'flag' => 'ALB'],
            ['name' => 'Algeria', 'alpha2' => 'DZ', 'alpha3' => 'DZA', 'dial' => '213', 'flag' => 'DZA'],
            ['name' => 'American Samoa', 'alpha2' => 'AS', 'alpha3' => 'ASM', 'dial' => '1684', 'flag' => 'ASM'],
            ['name' => 'Andorra', 'alpha2' => 'AD', 'alpha3' => 'AND', 'dial' => '376', 'flag' => 'AND'],
            ['name' => 'Angola', 'alpha2' => 'AO', 'alpha3' => 'AGO', 'dial' => '244', 'flag' => 'AGO'],
            ['name' => 'Anguilla', 'alpha2' => 'AI', 'alpha3' => 'AIA', 'dial' => '1264', 'flag' => 'AIA'],
            ['name' => 'Antarctica', 'alpha2' => 'AQ', 'alpha3' => 'ATA', 'dial' => '672', 'flag' => 'ATA'],
            ['name' => 'Antigua and Barbuda', 'alpha2' => 'AG', 'alpha3' => 'ATG', 'dial' => '1268', 'flag' => 'ATG'],
            ['name' => 'Argentina', 'alpha2' => 'AR', 'alpha3' => 'ARG', 'dial' => '54', 'flag' => 'ARG'],
            ['name' => 'Armenia', 'alpha2' => 'AM', 'alpha3' => 'ARM', 'dial' => '374', 'flag' => 'ARM'],
            ['name' => 'Aruba', 'alpha2' => 'AW', 'alpha3' => 'ABW', 'dial' => '297', 'flag' => 'ABW'],
            ['name' => 'Australia', 'alpha2' => 'AU', 'alpha3' => 'AUS', 'dial' => '61', 'flag' => 'AUS'],
            ['name' => 'Austria', 'alpha2' => 'AT', 'alpha3' => 'AUT', 'dial' => '43', 'flag' => 'AUT'],
            ['name' => 'Azerbaijan', 'alpha2' => 'AZ', 'alpha3' => 'AZE', 'dial' => '994', 'flag' => 'AZE'],
            ['name' => 'Bahamas', 'alpha2' => 'BS', 'alpha3' => 'BHS', 'dial' => '1242', 'flag' => 'BHS'],
            ['name' => 'Bahrain', 'alpha2' => 'BH', 'alpha3' => 'BHR', 'dial' => '973', 'flag' => 'BHR'],
            ['name' => 'Bangladesh', 'alpha2' => 'BD', 'alpha3' => 'BGD', 'dial' => '880', 'flag' => 'BGD'],
            ['name' => 'Barbados', 'alpha2' => 'BB', 'alpha3' => 'BRB', 'dial' => '1246', 'flag' => 'BRB'],
            ['name' => 'Belarus', 'alpha2' => 'BY', 'alpha3' => 'BLR', 'dial' => '375', 'flag' => 'BLR'],
            ['name' => 'Belgium', 'alpha2' => 'BE', 'alpha3' => 'BEL', 'dial' => '32', 'flag' => 'BEL'],
            ['name' => 'Belize', 'alpha2' => 'BZ', 'alpha3' => 'BLZ', 'dial' => '501', 'flag' => 'BLZ'],
            ['name' => 'Benin', 'alpha2' => 'BJ', 'alpha3' => 'BEN', 'dial' => '229', 'flag' => 'BEN'],
            ['name' => 'Bermuda', 'alpha2' => 'BM', 'alpha3' => 'BMU', 'dial' => '1441', 'flag' => 'BMU'],
            ['name' => 'Bhutan', 'alpha2' => 'BT', 'alpha3' => 'BTN', 'dial' => '975', 'flag' => 'BTN'],
            ['name' => 'Bolivia', 'alpha2' => 'BO', 'alpha3' => 'BOL', 'dial' => '591', 'flag' => 'BOL'],
            ['name' => 'Bosnia and Herzegovina', 'alpha2' => 'BA', 'alpha3' => 'BIH', 'dial' => '387', 'flag' => 'BIH'],
            ['name' => 'Botswana', 'alpha2' => 'BW', 'alpha3' => 'BWA', 'dial' => '267', 'flag' => 'BWA'],
            ['name' => 'Brazil', 'alpha2' => 'BR', 'alpha3' => 'BRA', 'dial' => '55', 'flag' => 'BRA'],
            ['name' => 'British Virgin Islands', 'alpha2' => 'VG', 'alpha3' => 'VGB', 'dial' => '1284', 'flag' => 'VGB'],
            ['name' => 'Brunei', 'alpha2' => 'BN', 'alpha3' => 'BRN', 'dial' => '673', 'flag' => 'BRN'],
            ['name' => 'Bulgaria', 'alpha2' => 'BG', 'alpha3' => 'BGR', 'dial' => '359', 'flag' => 'BGR'],
            ['name' => 'Burkina Faso', 'alpha2' => 'BF', 'alpha3' => 'BFA', 'dial' => '226', 'flag' => 'BFA'],
            ['name' => 'Burundi', 'alpha2' => 'BI', 'alpha3' => 'BDI', 'dial' => '257', 'flag' => 'BDI'],
            ['name' => 'Cambodia', 'alpha2' => 'KH', 'alpha3' => 'KHM', 'dial' => '855', 'flag' => 'KHM'],
            ['name' => 'Cameroon', 'alpha2' => 'CM', 'alpha3' => 'CMR', 'dial' => '237', 'flag' => 'CMR'],
            ['name' => 'Canada', 'alpha2' => 'CA', 'alpha3' => 'CAN', 'dial' => '1', 'flag' => 'CAN'],
            ['name' => 'Cape Verde', 'alpha2' => 'CV', 'alpha3' => 'CPV', 'dial' => '238', 'flag' => 'CPV'],
            ['name' => 'Cayman Islands', 'alpha2' => 'KY', 'alpha3' => 'CYM', 'dial' => '1345', 'flag' => 'CYM'],
            ['name' => 'Central African Republic', 'alpha2' => 'CF', 'alpha3' => 'CAF', 'dial' => '236', 'flag' => 'CAF'],
            ['name' => 'Chad', 'alpha2' => 'TD', 'alpha3' => 'TCD', 'dial' => '235', 'flag' => 'TCD'],
            ['name' => 'Chile', 'alpha2' => 'CL', 'alpha3' => 'CHL', 'dial' => '56', 'flag' => 'CHL'],
            ['name' => 'China', 'alpha2' => 'CN', 'alpha3' => 'CHN', 'dial' => '86', 'flag' => 'CHN'],
            ['name' => 'Colombia', 'alpha2' => 'CO', 'alpha3' => 'COL', 'dial' => '57', 'flag' => 'COL'],
            ['name' => 'Comoros', 'alpha2' => 'KM', 'alpha3' => 'COM', 'dial' => '269', 'flag' => 'COM'],
            ['name' => 'Congo', 'alpha2' => 'CG', 'alpha3' => 'COG', 'dial' => '242', 'flag' => 'COG'],
            ['name' => 'Cook Islands', 'alpha2' => 'CK', 'alpha3' => 'COK', 'dial' => '682', 'flag' => 'COK'],
            ['name' => 'Costa Rica', 'alpha2' => 'CR', 'alpha3' => 'CRI', 'dial' => '506', 'flag' => 'CRI'],
            ['name' => "Côte d'Ivoire", 'alpha2' => 'CI', 'alpha3' => 'CIV', 'dial' => '225', 'flag' => 'CIV'],
            ['name' => 'Croatia', 'alpha2' => 'HR', 'alpha3' => 'HRV', 'dial' => '385', 'flag' => 'HRV'],
            ['name' => 'Cuba', 'alpha2' => 'CU', 'alpha3' => 'CUB', 'dial' => '53', 'flag' => 'CUB'],
            ['name' => 'Cyprus', 'alpha2' => 'CY', 'alpha3' => 'CYP', 'dial' => '357', 'flag' => 'CYP'],
            ['name' => 'Czech Republic', 'alpha2' => 'CZ', 'alpha3' => 'CZE', 'dial' => '420', 'flag' => 'CZE'],
            ['name' => 'Democratic Republic of the Congo', 'alpha2' => 'CD', 'alpha3' => 'COD', 'dial' => '243', 'flag' => 'COD'],
            ['name' => 'Denmark', 'alpha2' => 'DK', 'alpha3' => 'DNK', 'dial' => '45', 'flag' => 'DNK'],
            ['name' => 'Djibouti', 'alpha2' => 'DJ', 'alpha3' => 'DJI', 'dial' => '253', 'flag' => 'DJI'],
            ['name' => 'Dominica', 'alpha2' => 'DM', 'alpha3' => 'DMA', 'dial' => '1767', 'flag' => 'DMA'],
            ['name' => 'Dominican Republic', 'alpha2' => 'DO', 'alpha3' => 'DOM', 'dial' => '1809', 'flag' => 'DOM'],
            ['name' => 'Ecuador', 'alpha2' => 'EC', 'alpha3' => 'ECU', 'dial' => '593', 'flag' => 'ECU'],
            ['name' => 'Egypt', 'alpha2' => 'EG', 'alpha3' => 'EGY', 'dial' => '20', 'flag' => 'EGY'],
            ['name' => 'El Salvador', 'alpha2' => 'SV', 'alpha3' => 'SLV', 'dial' => '503', 'flag' => 'SLV'],
            ['name' => 'Equatorial Guinea', 'alpha2' => 'GQ', 'alpha3' => 'GNQ', 'dial' => '240', 'flag' => 'GNQ'],
            ['name' => 'Eritrea', 'alpha2' => 'ER', 'alpha3' => 'ERI', 'dial' => '291', 'flag' => 'ERI'],
            ['name' => 'Estonia', 'alpha2' => 'EE', 'alpha3' => 'EST', 'dial' => '372', 'flag' => 'EST'],
            ['name' => 'Ethiopia', 'alpha2' => 'ET', 'alpha3' => 'ETH', 'dial' => '251', 'flag' => 'ETH'],
            ['name' => 'Falkland Islands', 'alpha2' => 'FK', 'alpha3' => 'FLK', 'dial' => '500', 'flag' => 'FLK'],
            ['name' => 'Faroe Islands', 'alpha2' => 'FO', 'alpha3' => 'FRO', 'dial' => '298', 'flag' => 'FRO'],
            ['name' => 'Fiji', 'alpha2' => 'FJ', 'alpha3' => 'FJI', 'dial' => '679', 'flag' => 'FJI'],
            ['name' => 'Finland', 'alpha2' => 'FI', 'alpha3' => 'FIN', 'dial' => '358', 'flag' => 'FIN'],
            ['name' => 'France', 'alpha2' => 'FR', 'alpha3' => 'FRA', 'dial' => '33', 'flag' => 'FRA'],
            ['name' => 'French Guiana', 'alpha2' => 'GF', 'alpha3' => 'GUF', 'dial' => '594', 'flag' => 'GUF'],
            ['name' => 'French Polynesia', 'alpha2' => 'PF', 'alpha3' => 'PYF', 'dial' => '689', 'flag' => 'PYF'],
            ['name' => 'Gabon', 'alpha2' => 'GA', 'alpha3' => 'GAB', 'dial' => '241', 'flag' => 'GAB'],
            ['name' => 'Gambia', 'alpha2' => 'GM', 'alpha3' => 'GMB', 'dial' => '220', 'flag' => 'GMB'],
            ['name' => 'Georgia', 'alpha2' => 'GE', 'alpha3' => 'GEO', 'dial' => '995', 'flag' => 'GEO'],
            ['name' => 'Germany', 'alpha2' => 'DE', 'alpha3' => 'DEU', 'dial' => '49', 'flag' => 'DEU'],
            ['name' => 'Ghana', 'alpha2' => 'GH', 'alpha3' => 'GHA', 'dial' => '233', 'flag' => 'GHA'],
            ['name' => 'Gibraltar', 'alpha2' => 'GI', 'alpha3' => 'GIB', 'dial' => '350', 'flag' => 'GIB'],
            ['name' => 'Greece', 'alpha2' => 'GR', 'alpha3' => 'GRC', 'dial' => '30', 'flag' => 'GRC'],
            ['name' => 'Greenland', 'alpha2' => 'GL', 'alpha3' => 'GRL', 'dial' => '299', 'flag' => 'GRL'],
            ['name' => 'Grenada', 'alpha2' => 'GD', 'alpha3' => 'GRD', 'dial' => '1473', 'flag' => 'GRD'],
            ['name' => 'Guadeloupe', 'alpha2' => 'GP', 'alpha3' => 'GLP', 'dial' => '590', 'flag' => 'GLP'],
            ['name' => 'Guam', 'alpha2' => 'GU', 'alpha3' => 'GUM', 'dial' => '1671', 'flag' => 'GUM'],
            ['name' => 'Guatemala', 'alpha2' => 'GT', 'alpha3' => 'GTM', 'dial' => '502', 'flag' => 'GTM'],
            ['name' => 'Guinea', 'alpha2' => 'GN', 'alpha3' => 'GIN', 'dial' => '224', 'flag' => 'GIN'],
            ['name' => 'Guinea-Bissau', 'alpha2' => 'GW', 'alpha3' => 'GNB', 'dial' => '245', 'flag' => 'GNB'],
            ['name' => 'Guyana', 'alpha2' => 'GY', 'alpha3' => 'GUY', 'dial' => '592', 'flag' => 'GUY'],
            ['name' => 'Haiti', 'alpha2' => 'HT', 'alpha3' => 'HTI', 'dial' => '509', 'flag' => 'HTI'],
            ['name' => 'Honduras', 'alpha2' => 'HN', 'alpha3' => 'HND', 'dial' => '504', 'flag' => 'HND'],
            ['name' => 'Hong Kong', 'alpha2' => 'HK', 'alpha3' => 'HKG', 'dial' => '852', 'flag' => 'HKG'],
            ['name' => 'Hungary', 'alpha2' => 'HU', 'alpha3' => 'HUN', 'dial' => '36', 'flag' => 'HUN'],
            ['name' => 'Iceland', 'alpha2' => 'IS', 'alpha3' => 'ISL', 'dial' => '354', 'flag' => 'ISL'],
            ['name' => 'India', 'alpha2' => 'IN', 'alpha3' => 'IND', 'dial' => '91', 'flag' => 'IND'],
            ['name' => 'Indonesia', 'alpha2' => 'ID', 'alpha3' => 'IDN', 'dial' => '62', 'flag' => 'IDN'],
            ['name' => 'Iran', 'alpha2' => 'IR', 'alpha3' => 'IRN', 'dial' => '98', 'flag' => 'IRN'],
            ['name' => 'Iraq', 'alpha2' => 'IQ', 'alpha3' => 'IRQ', 'dial' => '964', 'flag' => 'IRQ'],
            ['name' => 'Ireland', 'alpha2' => 'IE', 'alpha3' => 'IRL', 'dial' => '353', 'flag' => 'IRL'],
            ['name' => 'Israel', 'alpha2' => 'IL', 'alpha3' => 'ISR', 'dial' => '972', 'flag' => 'ISR'],
            ['name' => 'Italy', 'alpha2' => 'IT', 'alpha3' => 'ITA', 'dial' => '39', 'flag' => 'ITA'],
            ['name' => 'Jamaica', 'alpha2' => 'JM', 'alpha3' => 'JAM', 'dial' => '1876', 'flag' => 'JAM'],
            ['name' => 'Japan', 'alpha2' => 'JP', 'alpha3' => 'JPN', 'dial' => '81', 'flag' => 'JPN'],
            ['name' => 'Jordan', 'alpha2' => 'JO', 'alpha3' => 'JOR', 'dial' => '962', 'flag' => 'JOR'],
            ['name' => 'Kazakhstan', 'alpha2' => 'KZ', 'alpha3' => 'KAZ', 'dial' => '7', 'flag' => 'KAZ'],
            ['name' => 'Kenya', 'alpha2' => 'KE', 'alpha3' => 'KEN', 'dial' => '254', 'flag' => 'KEN'],
            ['name' => 'Kiribati', 'alpha2' => 'KI', 'alpha3' => 'KIR', 'dial' => '686', 'flag' => 'KIR'],
            ['name' => 'Kuwait', 'alpha2' => 'KW', 'alpha3' => 'KWT', 'dial' => '965', 'flag' => 'KWT'],
            ['name' => 'Kyrgyzstan', 'alpha2' => 'KG', 'alpha3' => 'KGZ', 'dial' => '996', 'flag' => 'KGZ'],
            ['name' => 'Laos', 'alpha2' => 'LA', 'alpha3' => 'LAO', 'dial' => '856', 'flag' => 'LAO'],
            ['name' => 'Latvia', 'alpha2' => 'LV', 'alpha3' => 'LVA', 'dial' => '371', 'flag' => 'LVA'],
            ['name' => 'Lebanon', 'alpha2' => 'LB', 'alpha3' => 'LBN', 'dial' => '961', 'flag' => 'LBN'],
            ['name' => 'Lesotho', 'alpha2' => 'LS', 'alpha3' => 'LSO', 'dial' => '266', 'flag' => 'LSO'],
            ['name' => 'Liberia', 'alpha2' => 'LR', 'alpha3' => 'LBR', 'dial' => '231', 'flag' => 'LBR'],
            ['name' => 'Libya', 'alpha2' => 'LY', 'alpha3' => 'LBY', 'dial' => '218', 'flag' => 'LBY'],
            ['name' => 'Liechtenstein', 'alpha2' => 'LI', 'alpha3' => 'LIE', 'dial' => '423', 'flag' => 'LIE'],
            ['name' => 'Lithuania', 'alpha2' => 'LT', 'alpha3' => 'LTU', 'dial' => '370', 'flag' => 'LTU'],
            ['name' => 'Luxembourg', 'alpha2' => 'LU', 'alpha3' => 'LUX', 'dial' => '352', 'flag' => 'LUX'],
            ['name' => 'Macau', 'alpha2' => 'MO', 'alpha3' => 'MAC', 'dial' => '853', 'flag' => 'MAC'],
            ['name' => 'Madagascar', 'alpha2' => 'MG', 'alpha3' => 'MDG', 'dial' => '261', 'flag' => 'MDG'],
            ['name' => 'Malawi', 'alpha2' => 'MW', 'alpha3' => 'MWI', 'dial' => '265', 'flag' => 'MWI'],
            ['name' => 'Malaysia', 'alpha2' => 'MY', 'alpha3' => 'MYS', 'dial' => '60', 'flag' => 'MYS'],
            ['name' => 'Maldives', 'alpha2' => 'MV', 'alpha3' => 'MDV', 'dial' => '960', 'flag' => 'MDV'],
            ['name' => 'Mali', 'alpha2' => 'ML', 'alpha3' => 'MLI', 'dial' => '223', 'flag' => 'MLI'],
            ['name' => 'Malta', 'alpha2' => 'MT', 'alpha3' => 'MLT', 'dial' => '356', 'flag' => 'MLT'],
            ['name' => 'Marshall Islands', 'alpha2' => 'MH', 'alpha3' => 'MHL', 'dial' => '692', 'flag' => 'MHL'],
            ['name' => 'Martinique', 'alpha2' => 'MQ', 'alpha3' => 'MTQ', 'dial' => '596', 'flag' => 'MTQ'],
            ['name' => 'Mauritania', 'alpha2' => 'MR', 'alpha3' => 'MRT', 'dial' => '222', 'flag' => 'MRT'],
            ['name' => 'Mauritius', 'alpha2' => 'MU', 'alpha3' => 'MUS', 'dial' => '230', 'flag' => 'MUS'],
            ['name' => 'Mexico', 'alpha2' => 'MX', 'alpha3' => 'MEX', 'dial' => '52', 'flag' => 'MEX'],
            ['name' => 'Micronesia', 'alpha2' => 'FM', 'alpha3' => 'FSM', 'dial' => '691', 'flag' => 'FSM'],
            ['name' => 'Moldova', 'alpha2' => 'MD', 'alpha3' => 'MDA', 'dial' => '373', 'flag' => 'MDA'],
            ['name' => 'Monaco', 'alpha2' => 'MC', 'alpha3' => 'MCO', 'dial' => '377', 'flag' => 'MCO'],
            ['name' => 'Mongolia', 'alpha2' => 'MN', 'alpha3' => 'MNG', 'dial' => '976', 'flag' => 'MNG'],
            ['name' => 'Montenegro', 'alpha2' => 'ME', 'alpha3' => 'MNE', 'dial' => '382', 'flag' => 'MNE'],
            ['name' => 'Montserrat', 'alpha2' => 'MS', 'alpha3' => 'MSR', 'dial' => '1664', 'flag' => 'MSR'],
            ['name' => 'Morocco', 'alpha2' => 'MA', 'alpha3' => 'MAR', 'dial' => '212', 'flag' => 'MAR'],
            ['name' => 'Mozambique', 'alpha2' => 'MZ', 'alpha3' => 'MOZ', 'dial' => '258', 'flag' => 'MOZ'],
            ['name' => 'Myanmar', 'alpha2' => 'MM', 'alpha3' => 'MMR', 'dial' => '95', 'flag' => 'MMR'],
            ['name' => 'Namibia', 'alpha2' => 'NA', 'alpha3' => 'NAM', 'dial' => '264', 'flag' => 'NAM'],
            ['name' => 'Nauru', 'alpha2' => 'NR', 'alpha3' => 'NRU', 'dial' => '674', 'flag' => 'NRU'],
            ['name' => 'Nepal', 'alpha2' => 'NP', 'alpha3' => 'NPL', 'dial' => '977', 'flag' => 'NPL'],
            ['name' => 'Netherlands', 'alpha2' => 'NL', 'alpha3' => 'NLD', 'dial' => '31', 'flag' => 'NLD'],
            ['name' => 'New Caledonia', 'alpha2' => 'NC', 'alpha3' => 'NCL', 'dial' => '687', 'flag' => 'NCL'],
            ['name' => 'New Zealand', 'alpha2' => 'NZ', 'alpha3' => 'NZL', 'dial' => '64', 'flag' => 'NZL'],
            ['name' => 'Nicaragua', 'alpha2' => 'NI', 'alpha3' => 'NIC', 'dial' => '505', 'flag' => 'NIC'],
            ['name' => 'Niger', 'alpha2' => 'NE', 'alpha3' => 'NER', 'dial' => '227', 'flag' => 'NER'],
            ['name' => 'Nigeria', 'alpha2' => 'NG', 'alpha3' => 'NGA', 'dial' => '234', 'flag' => 'NGA'],
            ['name' => 'North Korea', 'alpha2' => 'KP', 'alpha3' => 'PRK', 'dial' => '850', 'flag' => 'PRK'],
            ['name' => 'North Macedonia', 'alpha2' => 'MK', 'alpha3' => 'MKD', 'dial' => '389', 'flag' => 'MKD'],
            ['name' => 'Northern Mariana Islands', 'alpha2' => 'MP', 'alpha3' => 'MNP', 'dial' => '1670', 'flag' => 'MNP'],
            ['name' => 'Norway', 'alpha2' => 'NO', 'alpha3' => 'NOR', 'dial' => '47', 'flag' => 'NOR'],
            ['name' => 'Oman', 'alpha2' => 'OM', 'alpha3' => 'OMN', 'dial' => '968', 'flag' => 'OMN'],
            ['name' => 'Pakistan', 'alpha2' => 'PK', 'alpha3' => 'PAK', 'dial' => '92', 'flag' => 'PAK'],
            ['name' => 'Palau', 'alpha2' => 'PW', 'alpha3' => 'PLW', 'dial' => '680', 'flag' => 'PLW'],
            ['name' => 'Palestine', 'alpha2' => 'PS', 'alpha3' => 'PSE', 'dial' => '970', 'flag' => 'PSE'],
            ['name' => 'Panama', 'alpha2' => 'PA', 'alpha3' => 'PAN', 'dial' => '507', 'flag' => 'PAN'],
            ['name' => 'Papua New Guinea', 'alpha2' => 'PG', 'alpha3' => 'PNG', 'dial' => '675', 'flag' => 'PNG'],
            ['name' => 'Paraguay', 'alpha2' => 'PY', 'alpha3' => 'PRY', 'dial' => '595', 'flag' => 'PRY'],
            ['name' => 'Peru', 'alpha2' => 'PE', 'alpha3' => 'PER', 'dial' => '51', 'flag' => 'PER'],
            ['name' => 'Philippines', 'alpha2' => 'PH', 'alpha3' => 'PHL', 'dial' => '63', 'flag' => 'PHL'],
            ['name' => 'Poland', 'alpha2' => 'PL', 'alpha3' => 'POL', 'dial' => '48', 'flag' => 'POL'],
            ['name' => 'Portugal', 'alpha2' => 'PT', 'alpha3' => 'PRT', 'dial' => '351', 'flag' => 'PRT'],
            ['name' => 'Puerto Rico', 'alpha2' => 'PR', 'alpha3' => 'PRI', 'dial' => '1787', 'flag' => 'PRI'],
            ['name' => 'Qatar', 'alpha2' => 'QA', 'alpha3' => 'QAT', 'dial' => '974', 'flag' => 'QAT'],
            ['name' => 'Romania', 'alpha2' => 'RO', 'alpha3' => 'ROU', 'dial' => '40', 'flag' => 'ROU'],
            ['name' => 'Russia', 'alpha2' => 'RU', 'alpha3' => 'RUS', 'dial' => '7', 'flag' => 'RUS'],
            ['name' => 'Rwanda', 'alpha2' => 'RW', 'alpha3' => 'RWA', 'dial' => '250', 'flag' => 'RWA'],
            ['name' => 'Saint Kitts and Nevis', 'alpha2' => 'KN', 'alpha3' => 'KNA', 'dial' => '1869', 'flag' => 'KNA'],
            ['name' => 'Saint Lucia', 'alpha2' => 'LC', 'alpha3' => 'LCA', 'dial' => '1758', 'flag' => 'LCA'],
            ['name' => 'Saint Vincent and the Grenadines', 'alpha2' => 'VC', 'alpha3' => 'VCT', 'dial' => '1784', 'flag' => 'VCT'],
            ['name' => 'Samoa', 'alpha2' => 'WS', 'alpha3' => 'WSM', 'dial' => '685', 'flag' => 'WSM'],
            ['name' => 'San Marino', 'alpha2' => 'SM', 'alpha3' => 'SMR', 'dial' => '378', 'flag' => 'SMR'],
            ['name' => 'Saudi Arabia', 'alpha2' => 'SA', 'alpha3' => 'SAU', 'dial' => '966', 'flag' => 'SAU'],
            ['name' => 'Senegal', 'alpha2' => 'SN', 'alpha3' => 'SEN', 'dial' => '221', 'flag' => 'SEN'],
            ['name' => 'Serbia', 'alpha2' => 'RS', 'alpha3' => 'SRB', 'dial' => '381', 'flag' => 'SRB'],
            ['name' => 'Seychelles', 'alpha2' => 'SC', 'alpha3' => 'SYC', 'dial' => '248', 'flag' => 'SYC'],
            ['name' => 'Sierra Leone', 'alpha2' => 'SL', 'alpha3' => 'SLE', 'dial' => '232', 'flag' => 'SLE'],
            ['name' => 'Singapore', 'alpha2' => 'SG', 'alpha3' => 'SGP', 'dial' => '65', 'flag' => 'SGP'],
            ['name' => 'Slovakia', 'alpha2' => 'SK', 'alpha3' => 'SVK', 'dial' => '421', 'flag' => 'SVK'],
            ['name' => 'Slovenia', 'alpha2' => 'SI', 'alpha3' => 'SVN', 'dial' => '386', 'flag' => 'SVN'],
            ['name' => 'Solomon Islands', 'alpha2' => 'SB', 'alpha3' => 'SLB', 'dial' => '677', 'flag' => 'SLB'],
            ['name' => 'Somalia', 'alpha2' => 'SO', 'alpha3' => 'SOM', 'dial' => '252', 'flag' => 'SOM'],
            ['name' => 'South Africa', 'alpha2' => 'ZA', 'alpha3' => 'ZAF', 'dial' => '27', 'flag' => 'ZAF'],
            ['name' => 'South Korea', 'alpha2' => 'KR', 'alpha3' => 'KOR', 'dial' => '82', 'flag' => 'KOR'],
            ['name' => 'South Sudan', 'alpha2' => 'SS', 'alpha3' => 'SSD', 'dial' => '211', 'flag' => 'SSD'],
            ['name' => 'Spain', 'alpha2' => 'ES', 'alpha3' => 'ESP', 'dial' => '34', 'flag' => 'ESP'],
            ['name' => 'Sri Lanka', 'alpha2' => 'LK', 'alpha3' => 'LKA', 'dial' => '94', 'flag' => 'LKA'],
            ['name' => 'Sudan', 'alpha2' => 'SD', 'alpha3' => 'SDN', 'dial' => '249', 'flag' => 'SDN'],
            ['name' => 'Suriname', 'alpha2' => 'SR', 'alpha3' => 'SUR', 'dial' => '597', 'flag' => 'SUR'],
            ['name' => 'Sweden', 'alpha2' => 'SE', 'alpha3' => 'SWE', 'dial' => '46', 'flag' => 'SWE'],
            ['name' => 'Switzerland', 'alpha2' => 'CH', 'alpha3' => 'CHE', 'dial' => '41', 'flag' => 'CHE'],
            ['name' => 'Syria', 'alpha2' => 'SY', 'alpha3' => 'SYR', 'dial' => '963', 'flag' => 'SYR'],
            ['name' => 'Taiwan', 'alpha2' => 'TW', 'alpha3' => 'TWN', 'dial' => '886', 'flag' => 'TWN'],
            ['name' => 'Tajikistan', 'alpha2' => 'TJ', 'alpha3' => 'TJK', 'dial' => '992', 'flag' => 'TJK'],
            ['name' => 'Tanzania', 'alpha2' => 'TZ', 'alpha3' => 'TZA', 'dial' => '255', 'flag' => 'TZA'],
            ['name' => 'Thailand', 'alpha2' => 'TH', 'alpha3' => 'THA', 'dial' => '66', 'flag' => 'THA'],
            ['name' => 'Timor-Leste', 'alpha2' => 'TL', 'alpha3' => 'TLS', 'dial' => '670', 'flag' => 'TLS'],
            ['name' => 'Togo', 'alpha2' => 'TG', 'alpha3' => 'TGO', 'dial' => '228', 'flag' => 'TGO'],
            ['name' => 'Tonga', 'alpha2' => 'TO', 'alpha3' => 'TON', 'dial' => '676', 'flag' => 'TON'],
            ['name' => 'Trinidad and Tobago', 'alpha2' => 'TT', 'alpha3' => 'TTO', 'dial' => '1868', 'flag' => 'TTO'],
            ['name' => 'Tunisia', 'alpha2' => 'TN', 'alpha3' => 'TUN', 'dial' => '216', 'flag' => 'TUN'],
            ['name' => 'Turkey', 'alpha2' => 'TR', 'alpha3' => 'TUR', 'dial' => '90', 'flag' => 'TUR'],
            ['name' => 'Turkmenistan', 'alpha2' => 'TM', 'alpha3' => 'TKM', 'dial' => '993', 'flag' => 'TKM'],
            ['name' => 'Turks and Caicos Islands', 'alpha2' => 'TC', 'alpha3' => 'TCA', 'dial' => '1649', 'flag' => 'TCA'],
            ['name' => 'Tuvalu', 'alpha2' => 'TV', 'alpha3' => 'TUV', 'dial' => '688', 'flag' => 'TUV'],
            ['name' => 'Uganda', 'alpha2' => 'UG', 'alpha3' => 'UGA', 'dial' => '256', 'flag' => 'UGA'],
            ['name' => 'Ukraine', 'alpha2' => 'UA', 'alpha3' => 'UKR', 'dial' => '380', 'flag' => 'UKR'],
            ['name' => 'United Arab Emirates', 'alpha2' => 'AE', 'alpha3' => 'ARE', 'dial' => '971', 'flag' => 'ARE'],
            ['name' => 'United Kingdom', 'alpha2' => 'GB', 'alpha3' => 'GBR', 'dial' => '44', 'flag' => 'GBR'],
            ['name' => 'United States', 'alpha2' => 'US', 'alpha3' => 'USA', 'dial' => '1', 'flag' => 'USA'],
            ['name' => 'Uruguay', 'alpha2' => 'UY', 'alpha3' => 'URY', 'dial' => '598', 'flag' => 'URY'],
            ['name' => 'Uzbekistan', 'alpha2' => 'UZ', 'alpha3' => 'UZB', 'dial' => '998', 'flag' => 'UZB'],
            ['name' => 'Vanuatu', 'alpha2' => 'VU', 'alpha3' => 'VUT', 'dial' => '678', 'flag' => 'VUT'],
            ['name' => 'Vatican City', 'alpha2' => 'VA', 'alpha3' => 'VAT', 'dial' => '379', 'flag' => 'VAT'],
            ['name' => 'Venezuela', 'alpha2' => 'VE', 'alpha3' => 'VEN', 'dial' => '58', 'flag' => 'VEN'],
            ['name' => 'Vietnam', 'alpha2' => 'VN', 'alpha3' => 'VNM', 'dial' => '84', 'flag' => 'VNM'],
            ['name' => 'Yemen', 'alpha2' => 'YE', 'alpha3' => 'YEM', 'dial' => '967', 'flag' => 'YEM'],
            ['name' => 'Zambia', 'alpha2' => 'ZM', 'alpha3' => 'ZMB', 'dial' => '260', 'flag' => 'ZMB'],
            ['name' => 'Zimbabwe', 'alpha2' => 'ZW', 'alpha3' => 'ZWE', 'dial' => '263', 'flag' => 'ZWE'],
        ];
    }

    /** Find a registry entry by ISO alpha-2 code (case-insensitive). */
    public static function find(string $alpha2): ?array
    {
        $code = strtoupper(trim($alpha2));
        // Billing-registry alias: the app stores United Kingdom as UK.
        if ($code === 'UK') {
            $code = 'GB';
        }
        foreach (self::all() as $entry) {
            if ($entry['alpha2'] === $code) {
                return $entry;
            }
        }
        return null;
    }

    /** Dial code for an alpha-2 code, or null when unknown. */
    public static function dialCodeFor(string $alpha2): ?string
    {
        return self::find($alpha2)['dial'] ?? null;
    }

    public static function isSupported(string $alpha2): bool
    {
        return self::find($alpha2) !== null;
    }

    /** Case-insensitive name/code search for the searchable selector. */
    public static function search(string $query, int $limit = 50): array
    {
        $q = strtolower(trim($query));
        if ($q === '') {
            return array_slice(self::all(), 0, $limit);
        }
        $out = [];
        foreach (self::all() as $entry) {
            if (
                str_contains(strtolower($entry['name']), $q)
                || str_contains(strtolower($entry['alpha2']), $q)
                || str_contains(strtolower($entry['alpha3']), $q)
                || str_contains($entry['dial'], $q)
            ) {
                $out[] = $entry;
                if (count($out) >= $limit) {
                    break;
                }
            }
        }
        return $out;
    }

    /** Valid alpha-2 codes for validation rules. */
    public static function codes(): array
    {
        return array_column(self::all(), 'alpha2');
    }

    /**
     * Resolve free-form selector input ("GB — United Kingdom (+44)",
     * "United Kingdom", "GB", "GBR", "44") to a registry entry.
     * Used as the no-JS fallback when the hidden alpha-2 field is empty.
     */
    public static function resolveInput(string $text): ?array
    {
        $text = trim($text);
        if ($text === '') {
            return null;
        }
        if (preg_match('/^([A-Za-z]{2})\b/', $text, $m) && self::find($m[1])) {
            return self::find($m[1]);
        }
        $upper = strtoupper($text);
        foreach (self::all() as $entry) {
            if ($upper === $entry['alpha2'] || $upper === $entry['alpha3'] || strtoupper($entry['name']) === $upper) {
                return $entry;
            }
        }
        $digits = ltrim(preg_replace('/[^0-9]/', '', $text), '0');
        if ($digits !== '') {
            foreach (self::all() as $entry) {
                if ($entry['dial'] === $digits) {
                    return $entry;
                }
            }
        }
        return null;
    }
}
