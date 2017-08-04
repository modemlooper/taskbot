<?php
/**
 * BuddyPress xProfile Functions
 *
 * @package         APSA_SSO\xProfile
 * @version         1.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Setup xProfile data
 *
 * @since       1.0.0
 * @param       int $user_id User ID
 * @param       array $apsa_data Data returned from the API
 * @return      void
*/
function apsa_sso_set_xprofile_data( $user_id, $apsa_data ) {
	// Member data
	if ( isset( $apsa_data['Member']['Email'] ) ) {
		xprofile_set_field_data( 'Email', $user_id, $apsa_data['Member']['Email'] );
	}

	if ( isset( $apsa_data['Member']['Prefix'] ) ) {
		xprofile_set_field_data( 'Prefix', $user_id, $apsa_data['Member']['Prefix'] );
	}

	$name = isset( $apsa_data['Member']['FirstName'] ) ? $apsa_data['Member']['FirstName'] : '';

	if ( isset( $apsa_data['Member']['MiddleName'] ) ) {
		$name .= ' ' . $apsa_data['Member']['MiddleName'];
	}

	if ( isset( $apsa_data['Member']['LastName'] ) ) {
		$name .= ' ' . $apsa_data['Member']['LastName'];
	}

	xprofile_set_field_data( 'Full Name', $user_id, $name );

	if ( isset( $apsa_data['Member']['Suffix'] ) ) {
		xprofile_set_field_data( 'Suffix', $user_id, $apsa_data['Member']['Suffix'] );
	}

	if ( isset( $apsa_data['Member']['Credentials'] ) ) {
		xprofile_set_field_data( 'Credentials', $user_id, $apsa_data['Member']['Credentials'] );
	}

	if ( isset( $apsa_data['Member']['Title'] ) ) {
		xprofile_set_field_data( 'Title', $user_id, $apsa_data['Member']['Title'] );
	}

	if ( isset( $apsa_data['Member']['InformalName'] ) ) {
		xprofile_set_field_data( 'Informal Name', $user_id, $apsa_data['Member']['InformalName'] );
	}

	if ( isset( $apsa_data['Member']['CompanyName'] ) ) {
		xprofile_set_field_data( 'Company Name', $user_id, $apsa_data['Member']['CompanyName'] );
	}

	if ( isset( $apsa_data['Member']['CompanyDept'] ) ) {
		xprofile_set_field_data( 'Company Dept', $user_id, $apsa_data['Member']['CompanyDept'] );
	}

	if ( isset( $apsa_data['Member']['Phone1'] ) ) {
		xprofile_set_field_data( 'Phone', $user_id, $apsa_data['Member']['Phone1'] );
	}

	if ( isset( $apsa_data['Member']['Phone1Type'] ) ) {
		xprofile_set_field_data( 'Phone Type', $user_id, $apsa_data['Member']['Phone1Type'] );
	}

	if ( isset( $apsa_data['Member']['WebsiteUrl'] ) ) {
		xprofile_set_field_data( 'Website URL', $user_id, $apsa_data['Member']['WebsiteUrl'] );
	}

	if ( isset( $apsa_data['Member']['Address1'] ) ) {
		xprofile_set_field_data( 'Address 1', $user_id, $apsa_data['Member']['Address1'] );
	}

	if ( isset( $apsa_data['Member']['Address2'] ) ) {
		xprofile_set_field_data( 'Address 2', $user_id, $apsa_data['Member']['Address2'] );
	}

	if ( isset( $apsa_data['Member']['City'] ) ) {
		xprofile_set_field_data( 'City', $user_id, $apsa_data['Member']['City'] );
	}

	if ( isset( $apsa_data['Member']['StateProvince'] ) ) {
		xprofile_set_field_data( 'State', $user_id, $apsa_data['Member']['StateProvince'] );
	}

	if ( isset( $apsa_data['Member']['PostalCode'] ) ) {
		xprofile_set_field_data( 'Postal Code', $user_id, $apsa_data['Member']['PostalCode'] );
	}

	if ( isset( $apsa_data['Member']['CountryCode'] ) ) {
		xprofile_set_field_data( 'Country Code', $user_id, $apsa_data['Member']['CountryCode'] );
	}

	// Job history
	if ( isset( $apsa_data['JobHistory']['Company'] ) ) {
		xprofile_set_field_data( 'Company', $user_id, $apsa_data['JobHistory']['Company'] );
	}

	if ( isset( $apsa_data['JobHistory']['Position'] ) ) {
		xprofile_set_field_data( 'Position', $user_id, $apsa_data['JobHistory']['Position'] );
	}

	if ( isset( $apsa_data['JobHistory']['Department'] ) ) {
		xprofile_set_field_data( 'Department', $user_id, $apsa_data['JobHistory']['Department'] );
	}

	if ( isset( $apsa_data['JobHistory']['City'] ) ) {
		xprofile_set_field_data( 'City', $user_id, $apsa_data['JobHistory']['City'] );
	}

	if ( isset( $apsa_data['JobHistory']['State'] ) ) {
		xprofile_set_field_data( 'State', $user_id, $apsa_data['JobHistory']['State'] );
	}

	if ( isset( $apsa_data['JobHistory']['Country'] ) ) {
		xprofile_set_field_data( 'Country', $user_id, $apsa_data['JobHistory']['Country'] );
	}

	if ( isset( $apsa_data['JobHistory']['StartDate'] ) ) {
		xprofile_set_field_data( 'Start Date', $user_id, $apsa_data['JobHistory']['StartDate'] );
	}

	if ( isset( $apsa_data['JobHistory']['EndDate'] ) ) {
		xprofile_set_field_data( 'End Date', $user_id, $apsa_data['JobHistory']['EndDate'] );
	}

	if ( isset( $apsa_data['JobHistory']['URL'] ) ) {
		xprofile_set_field_data( 'URL', $user_id, $apsa_data['JobHistory']['URL'] );
	}

	if ( isset( $apsa_data['JobHistory']['Primary'] ) ) {
		xprofile_set_field_data( 'Primary', $user_id, $apsa_data['JobHistory']['Primary'] );
	}

	if ( isset( $apsa_data['JobHistory']['LinkedContact'] ) ) {
		xprofile_set_field_data( 'LinkedContact', $user_id, $apsa_data['JobHistory']['LinkedContact'] );
	}

	// Degrees
	// if ( $apsa_data['Degrees']['SchoolName'] ) {
	// 	xprofile_set_field_data( 'School Name', $user_id, $apsa_data['Degrees']['SchoolName'] );
	// }
	//
	// if ( $apsa_data['Degrees']['City'] ) {
	// 	xprofile_set_field_data( 'City', $user_id, $apsa_data['Degrees']['City'] );
	// }
	//
	// if ( $apsa_data['Degrees']['StateCode'] ) {
	// 	xprofile_set_field_data( 'State Code', $user_id, $apsa_data['Degrees']['StateCode'] );
	// }
	//
	// if ( $apsa_data['Degrees']['CountryCode'] ) {
	// 	xprofile_set_field_data( 'Country Code', $user_id, $apsa_data['Degrees']['CountryCode'] );
	// }
	//
	// if ( $apsa_data['Degrees']['Degree'] ) {
	// 	xprofile_set_field_data( 'Degree', $user_id, $apsa_data['Degrees']['Degree'] );
	// }
	//
	// if ( $apsa_data['Degrees']['DegreeDate'] ) {
	// 	xprofile_set_field_data( 'Degree Date', $user_id, $apsa_data['Degrees']['DegreeDate'] );
	// }
	//
	// if ( $apsa_data['Degrees']['Major'] ) {
	// 	xprofile_set_field_data( 'Major', $user_id, $apsa_data['Degrees']['Major'] );
	// }
	//
	// // Roles
	// if ( $apsa_data['Roles']['GroupCode'] ) {
	// 	xprofile_set_field_data( 'Group Code', $user_id, $apsa_data['Roles']['GroupCode'] );
	// }
	//
	// if ( $apsa_data['Roles']['MasterCode'] ) {
	// 	xprofile_set_field_data( 'Master Code', $user_id, $apsa_data['Roles']['MasterCode'] );
	// }
	//
	// if ( $apsa_data['Roles']['Description'] ) {
	// 	xprofile_set_field_data( 'Description', $user_id, $apsa_data['Roles']['Description'] );
	// }

	xprofile_clear_profile_data_object_cache( $user_id );
}
